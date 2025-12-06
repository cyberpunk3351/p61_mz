<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import MzLayout from '@/layouts/mz/Layout.vue';
import AddController from '@/actions/App/Http/Controllers/File/AddController';
import StoreController from '@/actions/App/Http/Controllers/File/StoreController';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import { Toaster } from '@/components/ui/sonner';
import 'vue-sonner/style.css';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Add',
        href: AddController.url(),
    },
];

const page = usePage();

const selectedFileName = ref<string>('');

const handleFileChange = (event: Event): void => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    selectedFileName.value = file?.name ?? '';
};

const toastBg = computed(() => {
    const statusId = page.props.flash?.status_id;

    if (statusId === 1) {
        return '#f87171';
    }

    return '#6ee7b7';
});

const message = computed(() => {
    return page.props.flash?.status ?? 'Saved';
});

const showToast = () => {
    toast(message.value, {
        style: {
            background: toastBg.value,
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Информация" />
        <MzLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Информация"
                    description="Обновить данные"
                />
                <Toaster />
                <Form
                    :action="StoreController.url()"
                    method="post"
                    enctype="multipart/form-data"
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="space-y-2">
                        <Label for="file">File</Label>
                        <Input
                            id="file"
                            type="file"
                            name="file"
                            accept=".csv,.txt"
                            required
                            @change="handleFileChange"
                        />
                        <p class="text-sm text-muted-foreground">
                            {{
                                selectedFileName
                                    ? `Вы выбрали: ${selectedFileName}`
                                    : 'Выберите файл в формате CSV или TXT (до 10 МБ).'
                            }}
                        </p>
                        <InputError :message="errors.file" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            :disabled="processing"
                            data-test="update-profile-button"
                            variant="outline"
                            @click="showToast"
                            >Save</Button
                        >

<!--                        <Transition-->
<!--                            enter-active-class="transition ease-in-out"-->
<!--                            enter-from-class="opacity-0"-->
<!--                            leave-active-class="transition ease-in-out"-->
<!--                            leave-to-class="opacity-0"-->
<!--                        >-->
<!--                            <p-->
<!--                                v-show="recentlySuccessful"-->
<!--                                class="text-sm text-neutral-600"-->
<!--                            >-->
<!--                                Saved-->
<!--                            </p>-->
<!--                        </Transition>-->
                    </div>
                </Form>
            </div>
        </MzLayout>
    </AppLayout>
</template>
