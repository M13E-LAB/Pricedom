@props(['type' => 'card', 'count' => 3])

@if($type === 'card')
    @for($i = 0; $i < $count; $i++)
        <div class="card animate-pulse">
            <div class="flex items-start space-x-4">
                <!-- Image Skeleton -->
                <div class="w-16 h-16 bg-white/10 rounded-lg flex-shrink-0"></div>
                
                <!-- Content Skeleton -->
                <div class="flex-1 space-y-2">
                    <div class="h-4 bg-white/10 rounded w-3/4"></div>
                    <div class="h-3 bg-white/10 rounded w-1/2"></div>
                    <div class="h-3 bg-white/10 rounded w-1/4"></div>
                </div>
                
                <!-- Price Skeleton -->
                <div class="space-y-2 text-right">
                    <div class="h-4 bg-white/10 rounded w-16"></div>
                    <div class="h-3 bg-white/10 rounded w-12"></div>
                </div>
            </div>
        </div>
    @endfor
@elseif($type === 'list')
    @for($i = 0; $i < $count; $i++)
        <div class="flex items-center space-x-4 p-4 animate-pulse">
            <div class="w-12 h-12 bg-white/10 rounded-lg flex-shrink-0"></div>
            <div class="flex-1 space-y-2">
                <div class="h-4 bg-white/10 rounded w-2/3"></div>
                <div class="h-3 bg-white/10 rounded w-1/3"></div>
            </div>
            <div class="h-4 bg-white/10 rounded w-16"></div>
        </div>
    @endfor
@elseif($type === 'text')
    <div class="space-y-3 animate-pulse">
        @for($i = 0; $i < $count; $i++)
            <div class="h-4 bg-white/10 rounded w-{{ ['full', '3/4', '1/2'][array_rand(['full', '3/4', '1/2'])] }}"></div>
        @endfor
    </div>
@endif

<style>
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>