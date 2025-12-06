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
import { Head, Link, WhenVisible } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { computed, ref, watch } from 'vue';

type Artist = {
    id: number;
    name: string;
    spotify_id: string | null;
    tracks_count: number;
    albums_count: number;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Artists',
        href: get().url,
    },
];

const props = defineProps<{
    artists: {
        data: Artist[];
        current_page: number;
        next_page_url: string | null;
    };
}>();

const loadedArtists = ref<Artist[]>([]);

watch(
    () => props.artists,
    (pagination) => {
        if (!pagination) {
            loadedArtists.value = [];
            return;
        }

        if (pagination.current_page === 1) {
            loadedArtists.value = [...pagination.data];
            return;
        }

        const existingIds = new Set(loadedArtists.value.map(({ id }) => id));

        pagination.data.forEach((artist) => {
            if (!existingIds.has(artist.id)) {
                loadedArtists.value.push(artist);
            }
        });
    },
    { immediate: true, deep: true },
);

const loadMoreParams = computed(() => {
    if (!props.artists?.next_page_url) {
        return null;
    }

    return {
        data: {
            page: props.artists.current_page + 1,
        },
        only: ['artists'],
        preserveScroll: true,
        preserveState: true,
        replace: true,
    };
});

const artistsAreEmpty = computed(() => loadedArtists.value.length === 0);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Artists" />
        <MzLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Artists"
                    description="Browse artists in the library"
                />
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Spotify</TableHead>
                            <TableHead>Albums</TableHead>
                            <TableHead>Tracks</TableHead>
                            <TableHead></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="artist in loadedArtists"
                            :key="artist.id"
                        >
                            <TableCell class="font-medium">
                                {{ artist.name }}
                            </TableCell>
                            <TableCell class="text-muted-foreground">
                                {{ artist.spotify_id ?? '—' }}
                            </TableCell>
                            <TableCell>
                                <Badge variant="outline">
                                    {{ artist.albums_count }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <Badge variant="secondary">
                                    {{ artist.tracks_count }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <Link :href="show.url(artist.id)">
                                    <Button size="sm">Open</Button>
                                </Link>
                            </TableCell>
                        </TableRow>
                        <TableRow
                            v-if="artistsAreEmpty"
                            class="text-center text-muted-foreground"
                        >
                            <TableCell colspan="5">
                                No artists found.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <div v-if="loadMoreParams" class="h-1 w-full">
                    <WhenVisible
                        :key="props.artists.current_page"
                        :always="true"
                        :params="loadMoreParams"
                    />
                </div>
            </div>
        </MzLayout>
    </AppLayout>
</template>
