@if ($errors->any())
    <div class="rounded-md border border-[#f0c9c4] bg-[#fff4f2] px-4 py-3 text-sm text-[#8a3027]">
        <p class="font-bold">Revisa estos campos:</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
