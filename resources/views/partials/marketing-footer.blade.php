<footer class="border-t border-[var(--trebbia-line)] bg-white px-5 py-12 lg:px-8">
    <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[1.25fr_0.8fr_0.9fr_1fr]">
        <div>
            <x-trebbia-logo class="w-72 sm:w-80" />
            <p class="mt-5 max-w-sm text-sm leading-6 text-[var(--trebbia-muted)]">
                Centro Inteligente de Reservas y Agendamiento para negocios que atienden por citas, WhatsApp, enlace público y recepción.
            </p>
            <p class="mt-5 text-sm font-semibold text-[var(--trebbia-ink)]">TREBBIA es desarrollada por GEINAPPS.</p>
        </div>

        <div>
            <h2 class="text-sm font-semibold uppercase tracking-[0.16em] text-[var(--trebbia-muted)]">Producto</h2>
            <nav class="mt-4 grid gap-3 text-sm" aria-label="Enlaces de producto">
                <a class="text-[var(--trebbia-muted)] hover:text-[var(--trebbia-petrol)]" href="{{ route('home') }}#plataforma">La plataforma</a>
                <a class="text-[var(--trebbia-muted)] hover:text-[var(--trebbia-petrol)]" href="{{ route('home') }}#funciona">Así funciona</a>
                <a class="text-[var(--trebbia-muted)] hover:text-[var(--trebbia-petrol)]" href="{{ route('home') }}#beneficios">Beneficios</a>
                <a class="text-[var(--trebbia-muted)] hover:text-[var(--trebbia-petrol)]" href="{{ route('home') }}#capacidades">Capacidades</a>
                <a class="text-[var(--trebbia-muted)] hover:text-[var(--trebbia-petrol)]" href="{{ route('home') }}#planes">Planes</a>
            </nav>
        </div>

        <div>
            <h2 class="text-sm font-semibold uppercase tracking-[0.16em] text-[var(--trebbia-muted)]">Confianza</h2>
            <nav class="mt-4 grid gap-3 text-sm" aria-label="Enlaces de confianza">
                <a class="text-[var(--trebbia-muted)] hover:text-[var(--trebbia-petrol)]" href="{{ route('legal.privacy') }}">Privacidad</a>
                <a class="text-[var(--trebbia-muted)] hover:text-[var(--trebbia-petrol)]" href="{{ route('legal.data') }}">Tratamiento de datos</a>
                <a class="text-[var(--trebbia-muted)] hover:text-[var(--trebbia-petrol)]" href="{{ route('legal.terms') }}">Términos y condiciones</a>
                <a class="text-[var(--trebbia-muted)] hover:text-[var(--trebbia-petrol)]" href="mailto:soporte@trebbia.app">Soporte</a>
            </nav>
        </div>

        <div>
            <h2 class="text-sm font-semibold uppercase tracking-[0.16em] text-[var(--trebbia-muted)]">Empresa</h2>
            <div class="mt-4 grid gap-3 text-sm text-[var(--trebbia-muted)]">
                <p>GEINAPPS</p>
                <p>Colombia</p>
                <a class="hover:text-[var(--trebbia-petrol)]" href="mailto:soporte@trebbia.app">soporte@trebbia.app</a>
                <p>© {{ now()->year }} TREBBIA. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</footer>
