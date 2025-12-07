<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import MzLayout from '@/layouts/mz/Layout.vue';
import { get, show } from '@/routes/artists';
import { type BreadcrumbItem } from '@/types';
import { Head, WhenVisible } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

type Album = {
    id: number;
    title: string;
    year: number | null;
    rating: number | null;
    img_64_url: string | null;
};

type AlbumCollection = {
    data: Album[];
};

type TrackArtist = {
    id: number;
    name: string;
};

type Track = {
    id: number;
    artist: Record<number, string>;
    artists: TrackArtist[];
    release_date: string | null;
    rating: number | null;
    title: string;
    albums: AlbumCollection;
};

type TracksPagination = {
    data: Track[];
    current_page: number;
    next_page_url: string | null;
};

type ArtistData = {
    id: number;
    name: string;
    spotify_id: string | null;
    tracks_count: number;
    albums_count: number;
    date: string;
    albums: AlbumCollection;
};

const props = defineProps<{
    artist: {
        data: ArtistData;
    };
    tracks: TracksPagination;
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: 'Artists',
        href: get().url,
    },
    {
        title: props.artist.data.name,
        href: show.url(props.artist.data.id),
    },
]);

const loadedTracks = ref<Track[]>([]);

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

const albumList = computed(() => props.artist.data.albums?.data ?? []);
const tracksAreEmpty = computed(() => loadedTracks.value.length === 0);

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
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.artist.data.name" />
        <MzLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    :title="props.artist.data.name"
                    description="Artist overview"
                />

                <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                    <div
                        class="rounded-lg border bg-card p-4 text-sm shadow-sm"
                    >
                        <p class="text-muted-foreground">Spotify</p>
                        <p class="mt-1 font-medium">
                            {{ props.artist.data.spotify_id ?? '—' }}
                        </p>
                    </div>
                    <div
                        class="rounded-lg border bg-card p-4 text-sm shadow-sm"
                    >
                        <p class="text-muted-foreground">Albums</p>
                        <div class="mt-1 flex items-center gap-2">
                            <Badge variant="outline">
                                {{ props.artist.data.albums_count }}
                            </Badge>
                            <span class="text-muted-foreground">
                                total releases
                            </span>
                        </div>
                    </div>
                    <div
                        class="rounded-lg border bg-card p-4 text-sm shadow-sm"
                    >
                        <p class="text-muted-foreground">Tracks</p>
                        <div class="mt-1 flex items-center gap-2">
                            <Badge variant="secondary">
                                {{ props.artist.data.tracks_count }}
                            </Badge>
                            <span class="text-muted-foreground">
                                linked tracks
                            </span>
                        </div>
                    </div>
                    <div
                        class="rounded-lg border bg-card p-4 text-sm shadow-sm"
                    >
                        <p class="text-muted-foreground">Created</p>
                        <p class="mt-1 font-medium">
                            {{ props.artist.data.date }}
                        </p>
                    </div>
                </div>

                <div class="space-y-3 rounded-lg border bg-card p-4">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold">Albums</p>
                        <Badge variant="outline">{{ albumList.length }}</Badge>
                    </div>
                    <div
                        v-if="albumList.length"
                        class="grid grid-cols-1 gap-3 md:grid-cols-2"
                    >
                        <div
                            v-for="album in albumList"
                            :key="album.id"
                            class="flex items-center gap-3 rounded-lg border p-3"
                        >
                            <div
                                class="flex size-12 items-center justify-center overflow-hidden rounded-md border bg-muted"
                            >
                                <img
                                    v-if="album.img_64_url"
                                    :src="album.img_64_url"
                                    :alt="album.title"
                                    class="size-full object-cover"
                                />
                                <span
                                    v-else
                                    class="text-xs font-semibold text-muted-foreground"
                                >
                                    {{ album.title.slice(0, 2).toUpperCase() }}
                                </span>
                            </div>
                            <div class="space-y-0.5">
                                <p class="font-semibold leading-tight">
                                    {{ album.title }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ album.year ?? 'Year unknown' }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Rating: {{ album.rating ?? '—' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <p
                        v-else
                        class="text-sm text-muted-foreground"
                    >
                        No albums linked yet.
                    </p>
                </div>

                <div class="rounded-lg border">
                    <div class="flex items-center justify-between px-4 py-3">
                        <p class="text-sm font-semibold">Tracks</p>
                        <Badge variant="secondary">
                            {{ props.artist.data.tracks_count }}
                        </Badge>
                    </div>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="text-xs">Title</TableHead>
                                <TableHead class="text-xs">Artists</TableHead>
                                <TableHead class="text-xs">Albums</TableHead>
                                <TableHead class="text-xs">
                                    Release date
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="track in loadedTracks"
                                :key="track.id"
                            >
                                <TableCell class="font-medium">
                                    {{ track.title }}
                                </TableCell>
                                <TableCell>
                                    {{
                                        track.artists?.length
                                            ? track.artists
                                                  .map(({ name }) => name)
                                                  .join(', ')
                                            : Object.values(track.artist ?? {}).join(', ')
                                    }}
                                </TableCell>
                                <TableCell class="max-w-[220px]">
                                    <span
                                        v-if="track.albums?.data?.length"
                                        class="line-clamp-2 text-sm text-muted-foreground"
                                    >
                                        {{
                                            track.albums.data
                                                .map((album) => album.title)
                                                .join(', ')
                                        }}
                                    </span>
                                    <span
                                        v-else
                                        class="text-sm text-muted-foreground"
                                    >
                                        —
                                    </span>
                                </TableCell>
                                <TableCell class="text-muted-foreground">
                                    {{ track.release_date ?? '—' }}
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="tracksAreEmpty">
                                <TableCell
                                    colspan="4"
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
            </div>
        </MzLayout>
    </AppLayout>
</template>
