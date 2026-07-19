{{--
    Prompt 21: Language Switcher Component
    Usage: <x-language-switcher />
    Renders an EN | বাংলা toggle in the navbar.
--}}
<div class="flex items-center gap-1" style="font-size:0.8rem;">
    <form method="POST" action="{{ route('language.switch') }}" class="inline">
        @csrf
        <input type="hidden" name="locale" value="en">
        <button type="submit"
                class="{{ app()->getLocale() === 'en' ? 'font-bold text-red-600' : 'text-gray-500 hover:text-gray-700' }} transition text-sm cursor-pointer border-none bg-transparent">
            EN
        </button>
    </form>
    <span class="text-gray-300 select-none">|</span>
    <form method="POST" action="{{ route('language.switch') }}" class="inline">
        @csrf
        <input type="hidden" name="locale" value="bn">
        <button type="submit"
                class="{{ app()->getLocale() === 'bn' ? 'font-bold text-red-600' : 'text-gray-500 hover:text-gray-700' }} transition text-sm cursor-pointer border-none bg-transparent">
            বাংলা
        </button>
    </form>
</div>
