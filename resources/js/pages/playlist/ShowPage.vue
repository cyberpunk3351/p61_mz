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
import { Button } from '@/components/ui/button';

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
}>();

const tracks = computed(() => props.tracks?.data ?? []);

const copyToClipboard = (id) => {
    const artist = document.getElementById('artist-' + id).innerText.trim();
    const song = document.getElementById('song-' + id).innerText.trim();
    let textToCopy = artist + ' - ' + song;
    const textarea = document.createElement('textarea')
    textarea.value = textToCopy
    document.body.appendChild(textarea)
    textarea.select()
    document.execCommand('copy')
    document.body.removeChild(textarea)
}

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.playlist.title" />
        <MzLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    :title="props.playlist.data.title"
                    description="Track list"
                />

                <pre>
                    {{ props.playlist }}
                </pre>


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
                        <TableRow v-for="track in props.playlist.data.tracks" :key="track.id">
                            <TableCell class="text-xs">{{ track.id }}</TableCell>
                            <TableCell :id="'artist-' + track.id" class="truncate max-w-[150px] text-xs">
                                {{ track.artist.join(', ') }}
                            </TableCell>
                            <TableCell :id="'song-' + track.id" class="text-xs">{{ track.title }}</TableCell>
                            <TableCell class="text-xs">{{ track.release_date ?? '—' }}</TableCell>
                            <TableCell>
                                <Button id="copyToClipboard" @click="copyToClipboard(track.id)"  variant="outline">
                                    Copy
                                </Button>
                            </TableCell>
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
