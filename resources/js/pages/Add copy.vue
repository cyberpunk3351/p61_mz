<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { store, index } from '@/routes/add';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Upload } from 'lucide-vue-next';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Add',
        href: index().url,
    },
];

const selectedFile = ref<File | null>(null);
const form = useForm({
    file: null as File | null,
});

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        selectedFile.value = file;
        form.file = file;
    }
};

</script>

<template>
    <Head title="Add File" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <Card class="mx-auto max-w-2xl">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Upload class="h-5 w-5" />
                        Upload File
                    </CardTitle>
                    <CardDescription>
                        Upload a file for processing. Supported formats: PDF,
                        DOC, DOCX, TXT, etc.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div
                        v-if="$page.props.flash.success"
                        class="rounded border border-green-400 bg-green-100 p-4 text-green-700"
                    >
                        {{ $page.props.flash.success }}
                    </div>

                    <div
                        v-if="form.errors.file"
                        class="rounded border border-red-400 bg-red-100 p-4 text-red-700"
                    >
                        {{ form.errors.file }}
                    </div>

                    <div
                        class="rounded-lg border-2 border-dashed border-gray-300 p-8 text-center dark:border-gray-600"
                    >
                        <input
                            type="file"
                            @change="handleFileSelect"
                            class="hidden"
                            id="file-upload"
                            accept=".pdf,.doc,.docx,.txt,.csv,.xlsx,.xls"
                        />
                        <label for="file-upload" class="cursor-pointer">
                            <div class="flex flex-col items-center gap-2">
                                <Upload class="h-12 w-12 text-gray-400" />
                                <p class="text-lg font-medium">
                                    {{
                                        selectedFile
                                            ? selectedFile.name
                                            : 'Click to upload or drag and drop'
                                    }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    PDF, DOC, DOCX, TXT, CSV, XLS, XLSX up to
                                    10MB
                                </p>
                            </div>
                        </label>
                    </div>

                    <div class="flex justify-end">
                        <Button
                            @click="handleUpload"
                            :disabled="!selectedFile || form.processing"
                            :loading="form.processing"
                        >
                            {{
                                form.processing ? 'Uploading...' : 'Upload File'
                            }}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
