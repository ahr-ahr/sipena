<header
    class="w-full border-b border-[#19140035] dark:border-[#3E3E3A]"
    x-data="{
        profileOpen: false,
        mobileSearch: false,
        ...globalSearch()
    }"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between gap-4">

        {{-- LEFT : LOGO --}}
        <div class="flex items-center gap-3 font-medium shrink-0">
            <img
                src="{{ env('MINIO_PUBLIC_URL') }}/sipena/public/logo-smktag.png"
                class="w-8 h-8 object-contain"
                alt="Logo"
            >
            <span class="hidden sm:inline">SIPENA</span>
        </div>

        {{-- CENTER : SEARCH (DESKTOP ONLY) --}}
        <div class="hidden md:flex flex-1 justify-center">
    <div class="relative w-full max-w-xl">

        <span
            class="material-symbols-outlined absolute left-3 top-1/2
                   -translate-y-1/2 text-[18px]
                   text-[#706f6c] dark:text-[#A1A09A]">
            search
        </span>

        <input
            type="text"
            placeholder="Cari laporan atau tiket..."
            class="w-full pl-10 pr-3 py-2 rounded-md
                   bg-white dark:bg-[#161615]
                   border border-[#19140035] dark:border-[#3E3E3A]
                   text-sm"
            @input.debounce.300ms="search($event.target.value)"
            @focus="open = true"
        >

        {{-- DROPDOWN RESULT --}}
        <div
            x-show="open"
            x-transition
            @click.outside="open = false"
            x-cloak
            class="absolute mt-2 w-full
                   rounded-xl overflow-hidden
                   bg-white dark:bg-[#161615]
                   border border-[#19140035] dark:border-[#3E3E3A]
                   shadow-xl z-50"
        >

            {{-- LAPORAN --}}
            <template x-if="results.laporan.length">
    <div>
        <div class="px-3 py-2 text-xs font-semibold text-gray-500">
            Laporan
        </div>

        <template x-for="item in results.laporan" :key="item.id">
            <button
                type="button"
                @click="openResult(item)"
                class="w-full text-left px-3 py-2 text-sm hover:bg-black/5 dark:hover:bg-white/10">

                <div class="font-medium" x-text="item.kode"></div>
                <div class="text-xs text-gray-500" x-text="item.judul"></div>
            </button>
        </template>
    </div>
</template>

            {{-- TICKETS --}}
            <template x-if="results.tickets.length">
    <div>
        <div class="px-3 py-2 text-xs font-semibold text-gray-500">
            Laporan
        </div>

        <template x-for="item in results.laporan" :key="item.id">
            <button
                type="button"
                @click="openResult(item)"
                class="w-full text-left px-3 py-2 text-sm hover:bg-black/5 dark:hover:bg-white/10">

                <div class="font-medium" x-text="item.kode"></div>
                <div class="text-xs text-gray-500" x-text="item.judul"></div>
            </button>
        </template>
    </div>
</template>
            <div
                x-show="!results.laporan.length && !results.tickets.length"
                class="px-4 py-4 text-sm text-center text-gray-500"
            >
                Tidak ada hasil
            </div>
        </div>
    </div>
</div>

        {{-- RIGHT : ACTIONS --}}
        <div class="flex items-center gap-1 shrink-0">

            {{-- SEARCH BUTTON (MOBILE ONLY) --}}
            <button
                @click="mobileSearch = !mobileSearch"
                class="p-2 rounded-sm hover:bg-black/5 dark:hover:bg-white/10 md:hidden"
                title="Cari"
            >
                <span class="material-symbols-outlined">search</span>
            </button>

            {{-- LANGUAGE --}}
            <a href="{{ route('lang.switch', app()->getLocale() === 'id' ? 'en' : 'id') }}"
               class="p-2 rounded-sm hover:bg-black/5 dark:hover:bg-white/10 transition"
               title="Ganti Bahasa">
                <span class="material-symbols-outlined">language</span>
            </a>

            {{-- NOTIFICATION --}}
            <div
                x-data="notificationBell(0)"
                x-init="init()"
                class="relative"
            >
                <button
                    @click="toggle()"
                    class="relative p-2 rounded-sm hover:bg-black/5 dark:hover:bg-white/10"
                    title="Notifikasi"
                    type="button"
                >
                    <span class="material-symbols-outlined">notifications</span>

                    <span
                        x-show="unreadCount > 0"
                        x-text="unreadCount"
                        class="absolute -top-1 -right-1
                               min-w-[16px] h-[16px]
                               px-1 text-[10px]
                               flex items-center justify-center
                               bg-red-500 text-white
                               rounded-full">
                    </span>
                </button>

                {{-- DROPDOWN --}}
                <div
                    x-show="open"
                    x-transition
                    @click.outside="open = false"
                    x-cloak
                    class="absolute right-0 mt-3 w-80
                           rounded-xl overflow-hidden
                           bg-white dark:bg-[#161615]
                           border border-[#19140035] dark:border-[#3E3E3A]
                           shadow-xl z-50"
                >
                    <div class="px-4 py-3 text-sm font-semibold border-b">
                        Notifikasi
                    </div>

                    <div class="max-h-80 overflow-y-auto divide-y">
                        <template x-for="notif in notifications" :key="notif.id">
                            <button
                                type="button"
                                @click="read(notif)"
                                class="w-full text-left px-4 py-3 text-sm
                                       hover:bg-black/5 dark:hover:bg-white/10"
                            >
                                <p class="font-medium" x-text="notif.notification.judul"></p>
                                <p class="text-xs text-gray-500 mt-1"
                                   x-text="notif.notification.pesan"></p>
                            </button>
                        </template>

                        <div
                            x-show="notifications.length === 0"
                            class="px-4 py-6 text-sm text-center text-gray-500"
                        >
                            Tidak ada notifikasi
                        </div>
                    </div>

                    <div class="px-4 py-2 text-xs text-center border-t">
                        <button
                            type="button"
                            @click="markAll()"
                            class="text-indigo-600 hover:underline"
                        >
                            Tandai semua sudah dibaca
                        </button>
                    </div>
                </div>
            </div>

            {{-- THEME --}}
            <button
                id="theme-toggle"
                class="p-2 rounded-sm hover:bg-black/5 dark:hover:bg-white/10 transition"
                title="Tema"
            >
                <span class="material-symbols-outlined dark:hidden">light_mode</span>
                <span class="material-symbols-outlined hidden dark:inline">dark_mode</span>
            </button>

            {{-- PROFILE --}}
            <div class="relative">
                <button
                    @click="profileOpen = !profileOpen"
                    class="p-2 rounded-full hover:bg-black/5 dark:hover:bg-white/10 transition"
                    title="Profil"
                >
                    <span class="material-symbols-outlined">account_circle</span>
                </button>

                <div
                    x-show="profileOpen"
                    x-transition
                    @click.outside="profileOpen = false"
                    x-cloak
                    class="absolute right-0 mt-2 w-44 rounded-lg
                           bg-white dark:bg-[#161615]
                           border border-[#19140035] dark:border-[#3E3E3A]
                           shadow-lg text-sm overflow-hidden"
                >
                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center gap-2 px-4 py-2
                              hover:bg-black/5 dark:hover:bg-white/10">
                        <span class="material-symbols-outlined text-[18px]">person</span>
                        Profile
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="w-full flex items-center gap-2 px-4 py-2
                                   hover:bg-black/5 dark:hover:bg-white/10">
                            <span class="material-symbols-outlined text-[18px]">logout</span>
                            Logout
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- MOBILE SEARCH BAR --}}
<div
    x-show="mobileSearch"
    x-transition
    x-cloak
    class="md:hidden px-4 pb-3"
>
    <div class="relative">
        <span
            class="material-symbols-outlined absolute left-3 top-1/2
                   -translate-y-1/2 text-[18px]">
            search
        </span>

        <input
            type="text"
            placeholder="Cari laporan atau tiket..."
            class="w-full pl-10 pr-3 py-2 rounded-md
                   bg-white dark:bg-[#161615]
                   border border-[#19140035] dark:border-[#3E3E3A]
                   text-sm"
            @input.debounce.300ms="search($event.target.value)"
            @focus="open = true"
        >

        {{-- DROPDOWN MOBILE --}}
        <div
            x-show="open"
            x-transition
            @click.outside="open = false"
            x-cloak
            class="absolute mt-2 w-full
                   rounded-xl overflow-hidden
                   bg-white dark:bg-[#161615]
                   border border-[#19140035] dark:border-[#3E3E3A]
                   shadow-xl z-50"
        >

            {{-- LAPORAN --}}
            <template x-if="results.laporan.length">
    <div>
        <div class="px-3 py-2 text-xs font-semibold text-gray-500">
            Laporan
        </div>

        <template x-for="item in results.laporan" :key="item.id">
            <button
                type="button"
                @click="openResult(item)"
                class="w-full text-left px-3 py-2 text-sm hover:bg-black/5 dark:hover:bg-white/10">

                <div class="font-medium" x-text="item.kode"></div>
                <div class="text-xs text-gray-500" x-text="item.judul"></div>
            </button>
        </template>
    </div>
</template>

            {{-- TICKETS --}}
            <template x-for="item in results.tickets" :key="item.id">
    <button
        type="button"
        @click="openResult(item)"
        class="w-full text-left px-3 py-2 text-sm hover:bg-black/5 dark:hover:bg-white/10">

        <div class="font-medium" x-text="item.kode"></div>
        <div class="text-xs text-gray-500" x-text="item.judul"></div>
    </button>
</template>


            <div
                x-show="!results.laporan.length && !results.tickets.length"
                class="px-4 py-4 text-sm text-center text-gray-500"
            >
                Tidak ada hasil
            </div>
        </div>
    </div>
</div>
</header>