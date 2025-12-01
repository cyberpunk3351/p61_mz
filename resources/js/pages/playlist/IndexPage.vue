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
import { get, show } from '@/routes/playlists';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import 'vue-sonner/style.css';
import { Button } from '@/components/ui/button';
import { computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Playlist',
        href: get().url,
    },
];

const props = defineProps<{
    playlists: {
        data: Array<{
            id: number;
            title: string;
            source: string | null;
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
}>();

const paginationLinks = computed(() =>
    props.playlists?.links?.filter((link) => link.url) ?? [],
);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Playlists" />
        <MzLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Playlists"
                    description="List of playlists"
                />
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>ID</TableHead>
                            <TableHead>Title</TableHead>
                            <TableHead>Source</TableHead>
                            <TableHead></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="item in props.playlists.data"
                            :key="item.id"
                        >
                            <TableCell>{{ item.id }}</TableCell>
                            <TableCell>{{ item.title }}</TableCell>
                            <TableCell>{{ item.source }}</TableCell>
                            <TableCell>
                                <Link :href="show(item.id)">
                                    <Button>Open</Button>
                                </Link>
                            </TableCell>
                        </TableRow>
                        <TableRow
                            v-if="props.playlists.data.length === 0"
                            class="text-center text-muted-foreground"
                        >
                            <TableCell colspan="4">No playlists found.</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </MzLayout>
    </AppLayout>
</template>
