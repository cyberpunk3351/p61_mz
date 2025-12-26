<script setup lang="ts">
import DetachTrackController from '@/actions/App/Http/Controllers/Playlist/DetachTrackController';
import UpdateRatingController from '@/actions/App/Http/Controllers/Track/UpdateRatingController';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

import AppLayout from '@/layouts/AppLayout.vue';
import MzLayout from '@/layouts/mz/Layout.vue';
import { show as showArtist } from '@/routes/artists';
import { get, show as showPlaylist } from '@/routes/playlists';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, WhenVisible, router } from '@inertiajs/vue3';
import 'vue-sonner/style.css';
import { computed, ref, watch } from 'vue';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { Info, Star } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';

type Album = {
    id: number;
    title: string;
};

type Artist = {
    id: number;
    name: string;
};

type AlbumCollection = {
    data: Album[];
};

type Track = {
    id: number;
    artist: Record<number, string>;
    artists: Artist[];
    release_date: string | null;
    rating: number | null;
    title: string;
    genres: string | null;
    albums: AlbumCollection;
};

type TracksPagination = {
    data: Track[];
    current_page: number;
    next_page_url: string | null;
};

type SortOption =
    | 'default'
    | 'rating_desc'
    | 'rating_asc'
    | 'artist_asc'
    | 'artist_desc';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Playlist',
        href: get().url,
    },
];

const props = defineProps<{
    playlist: {
        data: {
            id: number;
            title: string;
            source: string | null;
            date: string;
        };
    };
    tracks: TracksPagination;
}>();

const loadedTracks = ref<Track[]>([...props.tracks.data]);
const searchTerm = ref<string>('');
const hoverRatings = ref<Record<number, number | null>>({});
const updatingTrackId = ref<number | null>(null);
const removingTrackId = ref<number | null>(null);
const trackPendingRemoval = ref<Track | null>(null);
const removalDialogOpen = ref<boolean>(false);
const selectedArtistInitial = ref<string | null>(null);
const sortOption = ref<SortOption>('default');

watch(
    () => props.tracks,
    (pagination) => {
        if (!pagination) {
            loadedTracks.value = [];
            return;
        }

        if (pagination.current_page === 1) {
            loadedTracks.value = [...pagination.data];
            return;
        }

        const existingIds = new Set(loadedTracks.value.map(({ id }) => id));

        pagination.data.forEach((track) => {
            if (!existingIds.has(track.id)) {
                loadedTracks.value.push(track);
            }
        });
    },
    { immediate: true, deep: true },
);

const tracksAreEmpty = computed(() => loadedTracks.value.length === 0);
const sortLabel = computed(() => {
    const labels: Record<SortOption, string> = {
        default: 'Original order',
        rating_desc: 'Rating (high to low)',
        rating_asc: 'Rating (low to high)',
        artist_asc: 'Artist (A to Z)',
        artist_desc: 'Artist (Z to A)',
    };

    return labels[sortOption.value];
});
const getArtistNames = (track: Track): string[] =>
    track.artists?.map(({ name }) => name) ??
    Object.values(track.artist ?? {});
const availableArtistInitials = computed(() => {
    const initials = new Set<string>();

    loadedTracks.value.forEach((track) => {
        const name = primaryArtistName(track).trim();
        const firstLetter = name.charAt(0).toUpperCase();

        if (firstLetter) {
            initials.add(firstLetter);
        }
    });

    return Array.from(initials).sort((first, second) =>
        first.localeCompare(second),
    );
});
const filteredTracks = computed(() => {
    const term = searchTerm.value.toLowerCase();
    const hasSearch = Boolean(term.trim());
    const hasInitial = Boolean(selectedArtistInitial.value);

    return loadedTracks.value.filter((track) => {
        const titleMatch = track.title.toLowerCase().includes(term);

        // artist: { 393: "1991", 555: "Noisia" }
        const artistNames = getArtistNames(track);

        const artistMatch = hasSearch
            ? artistNames.some((name) => name.toLowerCase().includes(term))
            : true;

        const initialMatch = hasInitial
            ? (() => {
                  const name = primaryArtistName(track).trim();
                  const firstLetter = name.charAt(0).toUpperCase();

                  return (
                      firstLetter &&
                      firstLetter === selectedArtistInitial.value
                  );
              })()
            : true;

        if (hasSearch) {
            return (titleMatch || artistMatch) && initialMatch;
        }

        return initialMatch;
    });
});
const primaryArtistName = (track: Track): string => {
    if (track.artists?.length) {
        return track.artists[0].name;
    }

    const names = Object.values(track.artist ?? {});

    return names[0] ?? '';
};
const compareByArtistName = (first: Track, second: Track): number => {
    const firstName = primaryArtistName(first).toLowerCase();
    const secondName = primaryArtistName(second).toLowerCase();

    if (!firstName && !secondName) {
        return 0;
    }

    if (!firstName) {
        return 1;
    }

    if (!secondName) {
        return -1;
    }

    return firstName.localeCompare(secondName);
};
const sortedTracks = computed(() => {
    const tracks = [...filteredTracks.value];

    switch (sortOption.value) {
        case 'rating_desc':
            return tracks.sort((first, second) => {
                if (first.rating === null && second.rating === null) {
                    return 0;
                }

                if (first.rating === null) {
                    return 1;
                }

                if (second.rating === null) {
                    return -1;
                }

                return second.rating - first.rating;
            });
        case 'rating_asc':
            return tracks.sort((first, second) => {
                if (first.rating === null && second.rating === null) {
                    return 0;
                }

                if (first.rating === null) {
                    return 1;
                }

                if (second.rating === null) {
                    return -1;
                }

                return first.rating - second.rating;
            });
        case 'artist_asc':
            return tracks.sort((first, second) =>
                compareByArtistName(first, second),
            );
        case 'artist_desc':
            return tracks.sort((first, second) =>
                compareByArtistName(second, first),
            );
        default:
            return tracks;
    }
});

const loadMoreParams = computed(() => {
    if (!props.tracks?.next_page_url) {
        return null;
    }

    return {
        data: {
            page: props.tracks.current_page + 1,
        },
        only: ['tracks'],
        preserveScroll: true,
        preserveState: true,
        replace: true,
    };
});

const setHoverRating = (trackId: number, value: number | null): void => {
    hoverRatings.value = {
        ...hoverRatings.value,
        [trackId]: value,
    };
};

const starIsActive = (trackId: number, starValue: number): boolean => {
    const hoveredValue = hoverRatings.value[trackId];

    if (hoveredValue !== undefined && hoveredValue !== null) {
        return starValue <= hoveredValue;
    }

    const currentTrack = loadedTracks.value.find(({ id }) => id === trackId);

    if (currentTrack?.rating === null) {
        return false;
    }

    return starValue <= currentTrack.rating;
};

const ratingIsUpdating = (trackId: number): boolean =>
    updatingTrackId.value === trackId;

const trackIsRemoving = (trackId: number): boolean =>
    removingTrackId.value === trackId;

watch(removalDialogOpen, (isOpen) => {
    if (!isOpen) {
        trackPendingRemoval.value = null;
    }
});

const openRemovalDialog = (track: Track): void => {
    trackPendingRemoval.value = track;
    removalDialogOpen.value = true;
};

const setRating = (track: Track, rating: number): void => {
    if (ratingIsUpdating(track.id)) {
        return;
    }

    const previousRating = track.rating;

    track.rating = rating;
    updatingTrackId.value = track.id;

    router.patch(
        UpdateRatingController.url({ track: track.id }),
        { rating },
        {
            preserveScroll: true,
            preserveState: true,
            onError: () => {
                track.rating = previousRating;
            },
            onFinish: () => {
                updatingTrackId.value = null;
                setHoverRating(track.id, null);
            },
        },
    );
};

const removeTrackFromPlaylist = (track: Track): void => {
    if (trackIsRemoving(track.id)) {
        return;
    }

    removingTrackId.value = track.id;

    router.delete(
        DetachTrackController.url({
            playlist: props.playlist.data.id,
            track: track.id,
        }),
        {
            only: ['tracks'],
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                loadedTracks.value = loadedTracks.value.filter(
                    ({ id }) => id !== track.id,
                );
                removalDialogOpen.value = false;
                trackPendingRemoval.value = null;
            },
            onFinish: () => {
                removingTrackId.value = null;
            },
        },
    );
};

const confirmRemoval = (): void => {
    if (!trackPendingRemoval.value) {
        return;
    }

    removeTrackFromPlaylist(trackPendingRemoval.value);
};

const copyToClipboard = (track: Track): void => {
    const artistNames = getArtistNames(track);
    const textToCopy = `${artistNames.join(', ')} - ${track.title}`;

    if (navigator?.clipboard?.writeText) {
        void navigator.clipboard.writeText(textToCopy);
        return;
    }

    const textarea = document.createElement('textarea');
    textarea.value = textToCopy;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
};

const resetSearch = (): void => {
    searchTerm.value = '';
    loadedTracks.value = [];
    selectedArtistInitial.value = null;

    router.get(
        showPlaylist.url(props.playlist.data.id),
        {},
        {
            only: ['tracks'],
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.playlist.data.title" />
        <MzLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    :title="props.playlist.data.title"
                    description="Track list"
                />
                <div
                    class="grid grid-cols-1 gap-2 rounded-lg border bg-card p-4 text-sm"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-muted-foreground">ID</span>
                        <span class="font-medium">{{
                            props.playlist.data.id
                        }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-muted-foreground">Source</span>
                        <span class="font-medium">
                            {{ props.playlist.data.source ?? '—' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-muted-foreground">Created</span>
                        <span class="font-medium">{{
                            props.playlist.data.date
                        }}</span>
                    </div>
                </div>
                <div class="flex flex-col gap-4 rounded-lg border bg-card p-4">
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center"
                    >
                        <label
                            class="text-sm font-medium text-muted-foreground sm:w-40"
                        >
                            Search tracks
                        </label>
                        <div class="flex w-full items-center gap-2">
                            <Input
                                v-model="searchTerm"
                                name="search"
                                placeholder="Filter by title or artist"
                                class="w-full"
                                @keydown.escape.prevent="resetSearch"
                            />
                            <Button
                                variant="ghost"
                                type="button"
                                @click="resetSearch"
                            >
                                Reset
                            </Button>
                        </div>
                    </div>
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center"
                    >
                        <label
                            class="text-sm font-medium text-muted-foreground sm:w-40"
                        >
                            Sort tracks
                        </label>
                        <div class="flex w-full items-center gap-2">
                            <DropdownMenu>
                                <DropdownMenuTrigger :as-child="true">
                                    <Button
                                        variant="outline"
                                        class="w-full justify-between sm:w-60"
                                        type="button"
                                    >
                                        <span>Sort: {{ sortLabel }}</span>
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-56">
                                    <DropdownMenuRadioGroup
                                        v-model="sortOption"
                                    >
                                        <DropdownMenuRadioItem value="default">
                                            Original order
                                        </DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem
                                            value="rating_desc"
                                        >
                                            Rating (high to low)
                                        </DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem
                                            value="rating_asc"
                                        >
                                            Rating (low to high)
                                        </DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem
                                            value="artist_asc"
                                        >
                                            Artist (A to Z)
                                        </DropdownMenuRadioItem>
                                        <DropdownMenuRadioItem
                                            value="artist_desc"
                                        >
                                            Artist (Z to A)
                                        </DropdownMenuRadioItem>
                                    </DropdownMenuRadioGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center"
                    >
                        <label
                            class="text-sm font-medium text-muted-foreground sm:w-40"
                        >
                            Artist initial
                        </label>
                        <div
                            class="flex flex-wrap gap-2"
                            role="group"
                            aria-label="Filter tracks by artist initial"
                        >
                            <Button
                                type="button"
                                size="sm"
                                class="min-w-[52px]"
                                :aria-pressed="selectedArtistInitial === null"
                                :variant="
                                    selectedArtistInitial === null
                                        ? 'secondary'
                                        : 'outline'
                                "
                                @click="selectedArtistInitial = null"
                            >
                                All
                            </Button>
                            <Button
                                v-for="initial in availableArtistInitials"
                                :key="initial"
                                type="button"
                                size="sm"
                                class="min-w-[52px]"
                                :aria-pressed="selectedArtistInitial === initial"
                                :variant="
                                    selectedArtistInitial === initial
                                        ? 'secondary'
                                        : 'outline'
                                "
                                @click="selectedArtistInitial = initial"
                            >
                                {{ initial }}
                            </Button>
                        </div>
                    </div>
                </div>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="text-xs">Track</TableHead>
                            <TableHead class="text-right text-xs">
                                Actions
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="track in sortedTracks" :key="track.id">
                            <!--                            <pre>-->
                            <!--                                {{ track }}-->
                            <!--                            </pre>-->
                            <TableCell>
                                <Collapsible
                                    class="flex w-full flex-col gap-3 sm:w-[420px]"
                                >
                                    <div
                                        class="flex items-start justify-between gap-4 px-4 sm:items-center"
                                    >
                                        <div class="space-y-3">
                                            <div>
                                                <p
                                                    :id="'song-' + track.id"
                                                    class="font-bold"
                                                >
                                                    {{ track.title }}
                                                </p>
                                            </div>
                                            <div class="mb-1">
                                                <p
                                                    :id="'artist-' + track.id"
                                                    class="flex flex-wrap items-center gap-1 text-gray-300"
                                                >
                                                    <template
                                                        v-if="
                                                            track.artists
                                                                ?.length
                                                        "
                                                    >
                                                        <template
                                                            v-for="(
                                                                artist, index
                                                            ) in track.artists"
                                                            :key="artist.id"
                                                        >
                                                            <Link
                                                                :href="
                                                                    showArtist.url(
                                                                        artist.id,
                                                                    )
                                                                "
                                                                class="text-primary transition hover:text-primary/80 hover:underline"
                                                            >
                                                                {{
                                                                    artist.name
                                                                }}
                                                            </Link>
                                                            <span
                                                                v-if="
                                                                    index <
                                                                    track
                                                                        .artists
                                                                        .length -
                                                                        1
                                                                "
                                                                class="text-muted-foreground"
                                                            >
                                                                ,
                                                            </span>
                                                        </template>
                                                    </template>
                                                    <span
                                                        v-else
                                                        class="text-muted-foreground"
                                                        >—</span
                                                    >
                                                </p>
                                            </div>

                                            <div
                                                class="flex items-center gap-2 pt-2"
                                            >
                                                <div
                                                    class="flex items-center gap-1"
                                                >
                                                    <button
                                                        v-for="value in 5"
                                                        :key="value"
                                                        type="button"
                                                        class="p-1"
                                                        :disabled="
                                                            ratingIsUpdating(
                                                                track.id,
                                                            )
                                                        "
                                                        :aria-label="`Set rating to ${value}`"
                                                        @mouseover="
                                                            setHoverRating(
                                                                track.id,
                                                                value,
                                                            )
                                                        "
                                                        @focus="
                                                            setHoverRating(
                                                                track.id,
                                                                value,
                                                            )
                                                        "
                                                        @mouseleave="
                                                            setHoverRating(
                                                                track.id,
                                                                null,
                                                            )
                                                        "
                                                        @blur="
                                                            setHoverRating(
                                                                track.id,
                                                                null,
                                                            )
                                                        "
                                                        @click="
                                                            setRating(
                                                                track,
                                                                value,
                                                            )
                                                        "
                                                    >
                                                        <Star
                                                            :class="[
                                                                'size-5 transition-colors',
                                                                ratingIsUpdating(
                                                                    track.id,
                                                                )
                                                                    ? 'opacity-50'
                                                                    : '',
                                                                starIsActive(
                                                                    track.id,
                                                                    value,
                                                                )
                                                                    ? 'fill-amber-400 text-amber-400'
                                                                    : 'text-muted-foreground',
                                                            ]"
                                                        />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <CollapsibleTrigger>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="size-8"
                                            >
                                                <Info />
                                                <span class="sr-only"
                                                    >Toggle</span
                                                >
                                            </Button>
                                        </CollapsibleTrigger>
                                    </div>

                                    <CollapsibleContent>
                                        <div class="px-4">
                                            <p
                                                class="text-xs font-bold uppercase"
                                            >
                                                Artist:
                                            </p>
                                            <template
                                                v-if="track.artists?.length"
                                            >
                                                <div
                                                    v-for="artist in track.artists"
                                                    :key="artist.id"
                                                    class="px-4 text-xs"
                                                >
                                                    <Link
                                                        :href="
                                                            showArtist.url(
                                                                artist.id,
                                                            )
                                                        "
                                                        class="text-primary transition hover:text-primary/80 hover:underline"
                                                    >
                                                        {{ artist.name }}
                                                    </Link>
                                                </div>
                                            </template>
                                            <div
                                                v-else
                                                class="px-4 text-xs text-muted-foreground"
                                            >
                                                —
                                            </div>
                                            <p
                                                class="pt-1 text-xs font-bold uppercase"
                                            >
                                                Date:
                                            </p>
                                            <div class="px-4 text-xs">
                                                {{ track.release_date ?? '—' }}
                                            </div>
                                            <p
                                                class="pt-1 text-xs font-bold uppercase"
                                            >
                                                Album:
                                            </p>
                                            <div class="px-4 text-xs">
                                                {{
                                                    track.albums?.data?.[0]
                                                        ?.title ?? '—'
                                                }}
                                            </div>
                                        </div>
                                    </CollapsibleContent>
                                </Collapsible>
                            </TableCell>
                            <TableCell class="text-right">
                                <div
                                    class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end"
                                >
                                    <Button
                                        id="copyToClipboard"
                                        variant="outline"
                                        type="button"
                                        class="w-full sm:w-auto"
                                        @click="copyToClipboard(track)"
                                    >
                                        Copy
                                    </Button>
                                    <Button
                                        variant="destructive"
                                        type="button"
                                        class="w-full sm:w-auto"
                                        :disabled="trackIsRemoving(track.id)"
                                        @click="openRemovalDialog(track)"
                                    >
                                        <span v-if="trackIsRemoving(track.id)">
                                            Removing...
                                        </span>
                                        <span v-else>Remove</span>
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="tracksAreEmpty">
                            <TableCell
                                colspan="2"
                                class="text-center text-muted-foreground"
                            >
                                No tracks yet.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
                <div v-if="loadMoreParams" class="h-1 w-full">
                    <WhenVisible
                        :key="props.tracks.current_page"
                        :always="true"
                        :params="loadMoreParams"
                    />
                </div>
            </div>
        </MzLayout>

        <Dialog v-model:open="removalDialogOpen">
            <DialogContent class="max-w-lg">
                <DialogHeader class="space-y-3">
                    <DialogTitle>Remove track from playlist?</DialogTitle>
                    <DialogDescription>
                        This detaches the track from the playlist but keeps it
                        available in your library.
                    </DialogDescription>
                </DialogHeader>

                <div
                    v-if="trackPendingRemoval"
                    class="rounded-md border bg-muted/40 p-4 text-sm shadow-inner"
                >
                    <div
                        class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between sm:gap-3"
                    >
                        <div class="space-y-1">
                            <p class="font-semibold">
                                {{ trackPendingRemoval.title }}
                            </p>
                            <p class="text-muted-foreground">
                                {{
                                    getArtistNames(trackPendingRemoval).join(
                                        ', ',
                                    ) || '—'
                                }}
                            </p>
                        </div>
                        <div class="text-xs text-muted-foreground">
                            ID: {{ trackPendingRemoval.id }}
                        </div>
                    </div>
                </div>

                <DialogFooter
                    class="flex flex-col gap-2 sm:flex-row sm:justify-end"
                >
                    <DialogClose as-child>
                        <Button
                            variant="secondary"
                            class="w-full sm:w-auto"
                            :disabled="
                                trackPendingRemoval &&
                                trackIsRemoving(trackPendingRemoval.id)
                            "
                        >
                            Cancel
                        </Button>
                    </DialogClose>
                    <Button
                        variant="destructive"
                        class="w-full sm:w-auto"
                        :disabled="
                            trackPendingRemoval &&
                            trackIsRemoving(trackPendingRemoval.id)
                        "
                        @click="confirmRemoval"
                    >
                        <span
                            v-if="
                                trackPendingRemoval &&
                                trackIsRemoving(trackPendingRemoval.id)
                            "
                        >
                            Removing...
                        </span>
                        <span v-else>Remove track</span>
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
