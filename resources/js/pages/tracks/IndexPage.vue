<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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
import { index } from '@/routes/tracks';
import { type BreadcrumbItem } from '@/types';
import { Head, WhenVisible, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

type Album = {
    id: number;
    title: string;
};

type AlbumCollection = {
    data: Album[];
};

type Track = {
    id: number;
    title: string;
    artist: string[];
    release_date: string | null;
    rating: number | null;
    albums: AlbumCollection;
};

type TracksPagination = {
    data: Track[];
    current_page: number;
    next_page_url: string | null;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tracks',
        href: index().url,
    },
];

const props = defineProps<{
    tracks: TracksPagination;
    filters: {
        query: string | null;
    };
}>();

const searchTerm = ref(props.filters?.query ?? '');
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

const tracksAreEmpty = computed(() => loadedTracks.value.length === 0);

const loadMoreParams = computed(() => {
    if (!props.tracks?.next_page_url) {
        return null;
    }

    return {
        data: {
            page: props.tracks.current_page + 1,
            query: props.filters?.query || undefined,
        },
        only: ['tracks'],
        preserveScroll: true,
        preserveState: true,
        replace: true,
    };
});

const submitSearch = (): void => {
    router.get(
        index.url({
            query: searchTerm.value || undefined,
        }),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
};

const clearSearch = (): void => {
    searchTerm.value = '';
    submitSearch();
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Tracks" />

        <MzLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Tracks"
                    description="Search tracks with Meilisearch"
                />

                <form
                    class="flex flex-col gap-3 rounded-lg border bg-card p-4 md:flex-row md:items-center"
                    @submit.prevent="submitSearch"
                >
                    <div class="flex w-full flex-col gap-2 md:flex-row md:items-center">
                        <label class="text-sm font-medium text-muted-foreground md:w-32">
                            Search query
                        </label>
                        <Input
                            v-model="searchTerm"
                            name="query"
                            placeholder="Search by title or artist"
                            class="w-full"
                        />
                    </div>
                    <div class="flex items-center gap-2 md:justify-end">
                        <Button type="submit">Search</Button>
                        <Button
                            type="button"
                            variant="ghost"
                            @click="clearSearch"
                        >
                            Clear
                        </Button>
                    </div>
                </form>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Title</TableHead>
                            <TableHead>Artists</TableHead>
                            <TableHead>Release date</TableHead>
                            <TableHead>Rating</TableHead>
                            <TableHead>Album</TableHead>
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
                            <TableCell class="text-muted-foreground">
                                {{ track.artist.join(', ') }}
                            </TableCell>
                            <TableCell class="text-muted-foreground">
                                {{ track.release_date ?? '—' }}
                            </TableCell>
                            <TableCell class="text-muted-foreground">
                                {{ track.rating ?? '—' }}
                            </TableCell>
                            <TableCell class="text-muted-foreground">
                                {{
                                    track.albums?.data?.length
                                        ? track.albums.data[0].title
                                        : '—'
                                }}
                            </TableCell>
                        </TableRow>
                        <TableRow
                            v-if="tracksAreEmpty"
                            class="text-center text-muted-foreground"
                        >
                            <TableCell colspan="5">
                                No tracks found.
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
    </AppLayout>
</template>
