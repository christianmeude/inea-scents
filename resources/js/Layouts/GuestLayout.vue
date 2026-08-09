<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-brand-cream">
        <!-- Mesh gradient decorative blobs -->
        <div class="absolute -left-[10%] -top-[10%] h-[600px] w-[600px] rounded-full bg-burgundy-200/60 mix-blend-multiply blur-[120px] filter pointer-events-none sm:h-[800px] sm:w-[800px]"></div>
        
        <div class="absolute -right-[10%] -top-[10%] h-[600px] w-[600px] rounded-full bg-brand-primary/40 mix-blend-multiply blur-[120px] filter pointer-events-none sm:h-[700px] sm:w-[700px]"></div>
        
        <div class="absolute -bottom-[10%] -left-[10%] h-[600px] w-[600px] rounded-full bg-burgundy-900/50 mix-blend-multiply blur-[120px] filter pointer-events-none sm:h-[800px] sm:w-[800px]"></div>
        
        <div class="absolute -bottom-[10%] -right-[10%] h-[600px] w-[600px] rounded-full bg-brand-primary/50 mix-blend-multiply blur-[120px] filter pointer-events-none sm:h-[800px] sm:w-[800px]"></div>

        <!-- Center ambient soft glow to lift the foreground -->
        <div class="absolute left-1/2 top-1/2 h-[500px] w-[500px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-brand-cream/90 blur-[100px] filter pointer-events-none sm:h-[700px] sm:w-[700px]"></div>

        <div class="relative z-10 w-full max-w-sm px-6">
            <slot />
        </div>
    </div>
</template>
