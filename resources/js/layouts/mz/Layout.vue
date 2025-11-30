<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Separator } from '@/components/ui/separator';

</script>

<template>
    <div class="px-4 py-6">
        <div class="flex flex-col space-y-8 md:space-y-0 lg:flex-row lg:space-x-12 lg:space-y-0">

            <Separator class="my-6 md:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
