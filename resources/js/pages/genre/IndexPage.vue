<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import MzLayout from '@/layouts/mz/Layout.vue';
import { get, show } from '@/routes/genres';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Search, X, Music } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { debounce } from 'lodash';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';

const props = defineProps<{
    genres: {
        data: Array<{
            id: number;
            name: string;
            slug: string;
            tracks_count: number;
        }>;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        next_page_url: string | null;
        prev_page_url: string | null;
    };
    filters: {
        search: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Genres',
        href: get.url(),
    },
];

const searchQuery = ref(props.filters.search || '');

const handleSearch = debounce((value: string) => {
    router.get(
        get.url(),
        { search: value },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}, 300);

watch(searchQuery, (value) => {
    handleSearch(value);
});

function clearSearch() {
    searchQuery.value = '';
}

function handlePageChange(page: number) {
    router.get(
        get.url(),
        { page, search: searchQuery.value },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Genres" />
        <MzLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Genres"
                    description="Browse all music styles"
                />
            </div>

            <!-- Search Bar -->
            <div class="flex mb-6">
                <div class="relative flex w-full max-w-sm items-center">
                    <Input
                        v-model="searchQuery"
                        id="search"
                        type="text"
                        placeholder="Search genres..."
                        class="pl-10"
                    />
                    <span
                        class="absolute inset-y-0 start-0 flex items-center justify-center px-2"
                    >
                        <Search class="size-5 text-muted-foreground" />
                    </span>
                    <div v-if="searchQuery" class="absolute inset-y-0 end-0 flex items-center justify-center px-2">
                        <X
                            class="size-5 cursor-pointer text-muted-foreground hover:text-foreground"
                            @click="clearSearch"
                        />
                    </div>
                </div>
            </div>

            <!-- Genres List -->
            <div class="flex flex-col border border-border rounded-lg overflow-hidden bg-card/50">
                <Link
                    v-for="genre in genres.data"
                    :key="genre.id"
                    :href="show.url(genre.slug)"
                    class="group flex items-center justify-between p-4 hover:bg-accent/50 transition-all border-b last:border-0 border-border"
                >
                    <div class="flex items-center space-x-4">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary group-hover:bg-primary/20">
                            <Music class="h-4 w-4" />
                        </div>
                        <span class="font-medium text-sm text-foreground group-hover:text-primary transition-colors">
                            {{ genre.name }}
                        </span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-[10px] uppercase tracking-wider font-bold text-muted-foreground bg-zinc-800/50 px-2 py-1 rounded border border-border/50">
                            {{ genre.tracks_count }} tracks
                        </span>
                    </div>
                </Link>
            </div>

            <!-- Empty State -->
            <div v-if="genres.data.length === 0" class="flex flex-col items-center justify-center py-12 text-center text-muted-foreground">
                <Music class="mb-4 h-12 w-12 opacity-20" />
                <p>No genres found matching "{{ searchQuery }}"</p>
            </div>

            <!-- Pagination -->
            <div v-if="genres.total > genres.per_page" class="mt-8 flex justify-center">
                 <Pagination
                    v-slot="{ page }"
                    :items-per-page="genres.per_page"
                    :total="genres.total"
                    :sibling-count="1"
                    :show-edges="false"
                    :default-page="props.genres.current_page"
                    @update:page="handlePageChange"
                >
                    <PaginationContent>
                        <PaginationPrevious v-if="genres.prev_page_url" />
                        <PaginationItem v-else class="opacity-50 pointer-events-none">
                             <PaginationPrevious />
                        </PaginationItem>

                        <template v-for="(item, index) in Math.min(5, Math.ceil(genres.total / genres.per_page))" :key="index">
                             <!-- Simple pagination fallback if slot logic is complex, 
                                  but using the component's internal slot logic is better usually.
                                  Here we rely on the component to yield 'items' 
                             -->
                        </template>
                        <!-- Re-implementing correctly using the slot prop 'items' from Pagination component if available,
                             or relying on standard shadcn implementation structure 
                        -->
                         
                    </PaginationContent>
                    <!-- Correct Shadcn Pagination usage -->
                     <PaginationContent v-slot="{ items }">
                        <PaginationPrevious />
                        <template v-for="(item, index) in items" :key="index">
                            <PaginationItem v-if="item.type === 'page'" :value="item.value" :is-active="item.value === page">
                                {{ item.value }}
                            </PaginationItem>
                            <PaginationEllipsis v-else :index="index" />
                        </template>
                        <PaginationNext />
                    </PaginationContent>
                </Pagination>
            </div>
        </MzLayout>
    </AppLayout>
</template>
