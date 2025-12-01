<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
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
import { Head, Link } from '@inertiajs/vue3';
import 'vue-sonner/style.css';
import { computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Playlist',
        href: get().url,
    },
];

const props = defineProps<{
    playlist: {
        id: number;
        title: string;
        source: string | null;
        date: string;
    };
    tracks: {
        data: Array<{
            id: number;
            title: string;
            release_date: string | null;
            rating: number | null;
            spotify_id: string | null;
            isrc: string | null;
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
}>();

const tracks = computed(() => props.tracks?.data ?? []);

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.playlist.title" />
        <MzLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    :title="props.playlist.title"
                    description="Track list"
                />

<!--                <pre>-->
<!--                    {{ props.playlist.data.tracks }}-->
<!--                </pre>-->


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
                            <TableHead>ID</TableHead>
                            <TableHead>Artist</TableHead>
                            <TableHead>Title</TableHead>
                            <TableHead>Release date</TableHead>
                            <TableHead>Rating</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="track in props.playlist.data.tracks" :key="track.id">
                            <TableCell>{{ track.id }}</TableCell>
                            <TableCell :id="'artist-' + track.id" class="truncate max-w-[150px]">
                                {{ track.artist.join(', ') }}
                            </TableCell>
                            <TableCell>{{ track.title }}</TableCell>
                            <TableCell>{{ track.release_date ?? '—' }}</TableCell>
                            <TableCell>{{ track.rating ?? '—' }}</TableCell>
                        </TableRow>
                        <TableRow v-if="tracks.length === 0">
                            <TableCell
                                colspan="6"
                                class="text-center text-muted-foreground"
                            >
                                No tracks yet.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </MzLayout>
    </AppLayout>
</template>
