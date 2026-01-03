<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { show as showArtist } from '@/routes/artists';
import AppLayout from '@/layouts/AppLayout.vue';
import MzLayout from '@/layouts/mz/Layout.vue';
import { show } from '@/routes/genres';
import { rating } from '@/routes/tracks';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import 'vue-sonner/style.css';
import { ref } from 'vue';
import { Copy, Search, X } from 'lucide-vue-next';
const searchQuery = ref('');

import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuShortcut,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import { Toaster } from '@/components/ui/sonner';
import { Input } from '@/components/ui/input';
import { Checkbox } from '@/components/ui/checkbox';

type Album = {
    id: number;
    title: string;
};

type Artist = {
    id: number;
    name: string;
};

type Genre = {
    id: number;
    name: string;
    slug: string; // Added slug for linking
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
    genres: Genre[];
    primary_genre_id: number | null;
    albums: AlbumCollection;
};

type TracksPagination = {
    data: Track[];
    current_page: number;
    next_page_url: string | null;
    per_page: number | null;
    total: number;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Genres',
        href: '#', // TODO: Add index route for genres if needed
    },
];

const props = defineProps<{
    genre: {
        id: number;
        name: string;
        slug: string;
    };
    tracks: TracksPagination;
}>();

const sortByRating = ref();

function handlePageRatingChange() {
    // Basic sorting implementation - can be expanded
    router.get(
        show.url(props.genre.slug, {
            query: {
                page: 1,
                limit: 30,
            },
        }),
        {
            sort_by: 'rating',
            sort_direction: 'desc',
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
}

function handlePageChange(newPage: number) {
    router.get(
        show.url(props.genre.slug, {
            query: {
                page: newPage,
                limit: props.tracks.per_page,
            },
        }),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
}

function saveRating(rat: number, id: number) {
    router.patch(
        rating.url(id),
        { rating: rat },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
}

// Simplified search for now - can be added later
// const searchSaves = ...

function handleClick() {
    searchQuery.value = '';
    handlePageChange(1);
    searchQuery.value = '';
    sortByRating.value = false;
}
const copyToClipboard = (track: Track) => {
    const artist = track.artists.map((a) => a.name).join(', ');
    const textToCopy = `${artist} - ${track.title}`;

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard
            .writeText(textToCopy)
            .then(() => {
                toast.success('Copied to clipboard');
            })
            .catch(() => {
                fallbackCopy(textToCopy);
            });
    } else {
        fallbackCopy(textToCopy);
    }
};

const fallbackCopy = (text: string) => {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.top = '0';
    textarea.style.left = '0';
    textarea.style.position = 'fixed';
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();

    try {
        const successful = document.execCommand('copy');
        if (successful) {
            toast.success('Copied to clipboard');
        } else {
            toast.error('Failed to copy');
        }
    } catch (err) {
        console.error('Fallback copy failed', err);
        toast.error('Failed to copy');
    }

    document.body.removeChild(textarea);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.genre.name" />
        <MzLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    :title="props.genre.name"
                    description="Genre tracks"
                />
            </div>

            <div class="flex flex-col justify-between">
                <div class="mx-auto">
                    <Pagination
                        v-slot="{ page }"
                        :items-per-page="tracks.per_page"
                        :total="tracks.total"
                        :default-page="1"
                        @update:page="handlePageChange"
                    >
                        <PaginationContent v-slot="{ items }">
                            <PaginationPrevious />

                            <template
                                v-for="(item, index) in items"
                                :key="index"
                            >
                                <PaginationItem
                                    v-if="item.type === 'page'"
                                    :value="item.value"
                                    :is-active="item.value === page"
                                >
                                    {{ item.value }}
                                </PaginationItem>
                            </template>

                            <PaginationEllipsis :index="10" />

                            <PaginationNext />
                        </PaginationContent>
                    </Pagination>
                </div>
            </div>

            <div class="flex">
               <!-- Search can be added here -->
            </div>
            
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-[100px]"> ID </TableHead>

                        <TableHead> Artists </TableHead>

                        <TableHead> Title </TableHead>

                        <TableHead> Genres </TableHead>

                        <TableHead> Copy </TableHead>

                        <TableHead> Action </TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow
                        v-for="(item, index) in tracks.data"
                        :key="item.id"
                    >
                        <TableCell class="font-medium">
                            {{ item.id }}
                        </TableCell>

                        <TableCell class="max-w-[150px] truncate">
                            <div
                                v-for="artist in item.artists"
                                :key="artist.id"
                            >
                                <Link
                                    :href="showArtist.url(artist.id)"
                                    class="text-primary transition hover:text-primary/80 hover:underline"
                                >
                                    {{ artist.name }}
                                </Link>
                            </div>
                        </TableCell>

                        <TableCell class="max-w-[200px] truncate">
                            <p>{{ item.title }}</p>
                            <span class="text-xs text-zinc-500">
                                <div class="flex items-center">
                                    <div v-for="rat in 5" :key="rat">
                                        <svg
                                            @click="saveRating(rat, item.id)"
                                            class="ms-1 h-4 w-4"
                                            :class="[
                                                (item.rating ?? 0) >= rat
                                                    ? 'text-yellow-300'
                                                    : 'text-gray-500',
                                            ]"
                                            aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="currentColor"
                                            viewBox="0 0 22 20"
                                        >
                                            <path
                                                d="M20.924 7.625a1.523 1.523 0 0 0-1.238-1.044l-5.051-.734-2.259-4.577a1.534 1.534 0 0 0-2.752 0L7.365 5.847l-5.051.734A1.535 1.535 0 0 0 1.463 9.2l3.656 3.563-.863 5.031a1.532 1.532 0 0 0 2.226 1.616L11 17.033l4.518 2.375a1.534 1.534 0 0 0 2.226-1.617l-.863-5.03L20.537 9.2a1.523 1.523 0 0 0 .387-1.575Z"
                                            />
                                        </svg>
                                    </div>
                                </div>
                            </span>
                        </TableCell>

                        <TableCell class="max-w-[150px] truncate">
                            <div class="flex flex-wrap gap-1">
                                <Link
                                    v-for="genre in item.genres"
                                    :key="genre.id"
                                    :href="show.url(genre.slug)"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset transition hover:bg-zinc-700"
                                    :class="[
                                        genre.id === item.primary_genre_id
                                            ? 'bg-primary/10 text-primary ring-primary/30'
                                            : 'bg-zinc-800 text-zinc-400 ring-zinc-700',
                                    ]"
                                >
                                    {{ genre.name }}
                                </Link>
                            </div>
                        </TableCell>

                        <TableCell>
                            <Toaster />
                            <Button
                                id="copyToClipboard"
                                @click="copyToClipboard(item)"
                                variant="outline"
                                class="ml-auto"
                            >
                                <Copy />
                            </Button>
                        </TableCell>

                        <TableCell>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline"> Open </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent class="w-56" align="start">
                                    <DropdownMenuLabel>Link</DropdownMenuLabel>
                                    <DropdownMenuGroup>
                                        <DropdownMenuItem>
                                            <a
                                                target="_blank"
                                                :href="
                                                    'https://www.youtube.com/results?search_query=' +
                                                    item.artists[0].name +
                                                    '+-+' +
                                                    item.title
                                                "
                                            >
                                                Youtube
                                            </a>
                                            <DropdownMenuShortcut
                                                ><svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    x="0px"
                                                    y="0px"
                                                    width="100"
                                                    height="100"
                                                    viewBox="0 0 48 48"
                                                >
                                                    <path
                                                        fill="#FF3D00"
                                                        d="M43.2,33.9c-0.4,2.1-2.1,3.7-4.2,4c-3.3,0.5-8.8,1.1-15,1.1c-6.1,0-11.6-0.6-15-1.1c-2.1-0.3-3.8-1.9-4.2-4C4.4,31.6,4,28.2,4,24c0-4.2,0.4-7.6,0.8-9.9c0.4-2.1,2.1-3.7,4.2-4C12.3,9.6,17.8,9,24,9c6.2,0,11.6,0.6,15,1.1c2.1,0.3,3.8,1.9,4.2,4c0.4,2.3,0.9,5.7,0.9,9.9C44,28.2,43.6,31.6,43.2,33.9z"
                                                    ></path>
                                                    <path
                                                        fill="#FFF"
                                                        d="M20 31L20 17 32 24z"
                                                    ></path></svg
                                            ></DropdownMenuShortcut>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem>
                                            <a
                                                target="_blank"
                                                :href="
                                                    'https://vk.com/audios6275562?q=' +
                                                    item.artists[0].name +
                                                    ' - ' +
                                                    item.title
                                                "
                                                >Vk</a
                                            >
                                            <DropdownMenuShortcut
                                                ><svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    x="0px"
                                                    y="0px"
                                                    width="100"
                                                    height="100"
                                                    viewBox="0 0 48 48"
                                                >
                                                    <path
                                                        fill="#1976d2"
                                                        d="M42,37c0,2.762-2.238,5-5,5H11c-2.761,0-5-2.238-5-5V11c0-2.762,2.239-5,5-5h26c2.762,0,5,2.238,5,5 V37z"
                                                    ></path>
                                                    <path
                                                        fill="#fff"
                                                        d="M35.937,18.041c0.046-0.151,0.068-0.291,0.062-0.416C35.984,17.263,35.735,17,35.149,17h-2.618 c-0.661,0-0.966,0.4-1.144,0.801c0,0-1.632,3.359-3.513,5.574c-0.61,0.641-0.92,0.625-1.25,0.625C26.447,24,26,23.786,26,23.199 v-5.185C26,17.32,25.827,17,25.268,17h-4.649C20.212,17,20,17.32,20,17.641c0,0.667,0.898,0.827,1,2.696v3.623 C21,24.84,20.847,25,20.517,25c-0.89,0-2.642-3-3.815-6.932C16.448,17.294,16.194,17,15.533,17h-2.643 C12.127,17,12,17.374,12,17.774c0,0.721,0.6,4.619,3.875,9.101C18.25,30.125,21.379,32,24.149,32c1.678,0,1.85-0.427,1.85-1.094 v-2.972C26,27.133,26.183,27,26.717,27c0.381,0,1.158,0.25,2.658,2c1.73,2.018,2.044,3,3.036,3h2.618 c0.608,0,0.957-0.255,0.971-0.75c0.003-0.126-0.015-0.267-0.056-0.424c-0.194-0.576-1.084-1.984-2.194-3.326 c-0.615-0.743-1.222-1.479-1.501-1.879C32.062,25.36,31.991,25.176,32,25c0.009-0.185,0.105-0.361,0.249-0.607 C32.223,24.393,35.607,19.642,35.937,18.041z"
                                                    ></path></svg
                                            ></DropdownMenuShortcut>
                                        </DropdownMenuItem>
                                    </DropdownMenuGroup>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </MzLayout>
    </AppLayout>
</template>
