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
import { Head } from '@inertiajs/vue3';
import 'vue-sonner/style.css';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Playlist',
        href: get().url,
    },
];

const props = defineProps<{
    playlist: Object;
}>();
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
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="item in props.playlist.data"
                            :key="item.id"
                        >
                            <TableCell>{{ item.id }}</TableCell>
                            <TableCell>{{ item.title }}</TableCell>
                            <TableCell>{{ item.source }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </MzLayout>
    </AppLayout>
</template>
