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
import { Head, WhenVisible} from '@inertiajs/vue3';
import 'vue-sonner/style.css';
import { computed, ref, watch } from 'vue';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible'
import { Info } from 'lucide-vue-next'

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
                            <TableHead class="text-xs">Artist</TableHead>
                            <TableHead class="text-xs"></TableHead>
                        </TableRow>
                    </TableHeader>
                    <pre>
<!--                        {{ loadedTracks }}-->
                    </pre>
                    <TableBody>
                        <TableRow v-for="track in loadedTracks" :key="track.id">
                            <TableCell>


                                <Collapsible class="flex w-[350px] flex-col gap-2">
                                    <div class="flex items-center justify-between gap-4 px-4">
                                        <div>
                                            <div class="mb-1">
                                                <p :id="'artist-' + track.id" class="font-bold">{{ track.artist.join(', ') }} </p>
                                            </div>

                                            <div>
                                                <p :id="'song-' + track.id" class="text-gray-300">{{ track.title }}
                                                </p>
                                            </div>
                                        </div>
                                        <CollapsibleTrigger>
                                            <Button variant="ghost" size="icon" class="size-8">
                                                <Info />
                                                <span class="sr-only">Toggle</span>
                                            </Button>
                                        </CollapsibleTrigger>
                                    </div>

                                    <CollapsibleContent>
                                        <div class="px-4">
                                            <p class="text-xs font-bold uppercase">Artist:</p>
                                            <div v-for="item in track.artist" :key="item" class="px-4 text-xs" >
                                                {{ item }}
                                            </div>
                                            <p class="text-xs font-bold pt-1 uppercase">Date:</p>
                                            <div class="px-4 text-xs">
                                                {{ track.release_date ?? '—' }}
                                            </div>
                                            <p class="text-xs font-bold pt-1 uppercase">Album:</p>
                                            <div class="px-4 text-xs">
                                                {{ track.albums.data[0].title ?? '—' }}
                                            </div>
                                        </div>
                                    </CollapsibleContent>
                                </Collapsible>

                            </TableCell>
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
