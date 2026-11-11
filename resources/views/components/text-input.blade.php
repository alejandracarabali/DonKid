@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'min-h-[48px] rounded-xl border-2 border-line bg-white px-3 text-base text-ink focus:border-action focus:ring-action disabled:bg-canvas disabled:text-soft']) }}>