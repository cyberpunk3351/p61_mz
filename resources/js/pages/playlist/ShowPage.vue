<script setup lang="ts">
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
import AppLayout from '@/layouts/AppLayout.vue';
import MzLayout from '@/layouts/mz/Layout.vue';
import { get } from '@/routes/playlists';
import { type BreadcrumbItem } from '@/types';
import { Head, WhenVisible } from '@inertiajs/vue3';
import 'vue-sonner/style.css';
import { computed, ref, watch } from 'vue';

type Track = {
    id: number;
    artist: string[];
    release_date: string | null;
    rating: number | null;
    title: string;
    genres: string | null;
};

type TracksPagination = {
    data: Track[];
    current_page: number;
    next_page_url: string | null;
};

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
const tracksAreEmpty = ref(false);

const loadMoreParams = computed(() => {
    if (!props.tracks?.next_page_url) {
        tracksAreEmpty.value = true;
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

const copyToClipboard = (id: number): void => {
    const artist = document.getElementById('artist-' + id)?.innerText.trim() ?? '';
    const song = document.getElementById('song-' + id)?.innerText.trim() ?? '';
    const textToCopy = artist + ' - ' + song;
    const textarea = document.createElement('textarea');
    textarea.value = textToCopy;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
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
                <div class="grid grid-cols-1 gap-2 rounded-lg border bg-card p-4 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-muted-foreground">ID</span>
                        <span class="font-medium">{{ props.playlist.data.id }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-muted-foreground">Source</span>
                        <span class="font-medium">
                            {{ props.playlist.data.source ?? '—' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-muted-foreground">Created</span>
                        <span class="font-medium">{{ props.playlist.data.date }}</span>
                    </div>
                </div>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="text-xs">ID</TableHead>
                            <TableHead class="text-xs">Artist</TableHead>
                            <TableHead class="text-xs">Title</TableHead>
                            <TableHead class="text-xs">Date</TableHead>
                            <TableHead class="text-xs"></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="track in loadedTracks" :key="track.id">
                            <TableCell class="text-xs">{{ track.id }}</TableCell>
                            <TableCell :id="'artist-' + track.id" class="max-w-[150px] truncate text-xs">
                                {{ track.artist.join(', ') }}
                            </TableCell>
                            <TableCell :id="'song-' + track.id" class="text-xs">{{ track.title }}</TableCell>
                            <TableCell class="text-xs">{{ track.release_date ?? '—' }}</TableCell>
                            <TableCell>
                                <Button id="copyToClipboard" @click="copyToClipboard(track.id)" variant="outline">
                                    Copy
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="tracksAreEmpty">
                            <TableCell
                                colspan="6"
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
    </AppLayout>
</template>
