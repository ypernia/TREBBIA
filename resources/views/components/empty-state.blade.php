@props(['icon' => 'plus', 'title', 'body' => null, 'action' => null, 'actionLabel' => null])

<div {{ $attributes->merge(['class' => 'p-8 text-center']) }}>
    <div class="mx-auto flex size-12 items-center justify-center rounded-md bg-[#edf7f4] text-[#245f57]">
        <x-icon :name="$icon" class="size-5" />
    </div>
    <p class="mt-4 font-bold">{{ $title }}</p>
    @if ($body)
        <p class="mx-auto mt-2 max-w-md text-sm text-[#64716d]">{{ $body }}</p>
    @endif
    @if ($action && $actionLabel)
        <a class="trebbia-button mt-5" href="{{ $action }}">
            <x-icon name="plus" class="size-4" />
            {{ $actionLabel }}
        </a>
    @endif
</div>
