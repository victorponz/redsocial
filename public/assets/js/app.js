TimeAgo.addDefaultLocale({
    locale: 'es',
    now: {
        now: {
            current: "ahora mismo",
            future: "en un momento",
            past: "hace un momento"
        }
    },
    long: {
        second: {
            past: { one: "hace {0} segundo", other: "hace {0} segundos" },
            future: { one: "dentro de {0} segundo", other: "dentro de {0} segundos" }
        },
        minute: {
            past: { one: "hace {0} minuto", other: "hace {0} minutos" },
            future: { one: "dentro de {0} minuto", other: "dentro de {0} minutos" }
        },
        hour: {
            past: { one: "hace {0} hora", other: "hace {0} horas" },
            future: { one: "dentro de {0} hora", other: "dentro de {0} horas" }
        },
        day: {
            past: { one: "hace {0} día", other: "hace {0} días" },
            future: { one: "dentro de {0} día", other: "dentro de {0} días" }
        },
        week: {
            past: { one: "hace {0} semana", other: "hace {0} semanas" },
            future: { one: "dentro de {0} semana", other: "dentro de {0} semanas" }
        },
        month: {
            past: { one: "hace {0} mes", other: "hace {0} meses" },
            future: { one: "dentro de {0} mes", other: "dentro de {0} meses" }
        },
        year: {
            past: { one: "hace {0} año", other: "hace {0} años" },
            future: { one: "dentro de {0} año", other: "dentro de {0} años" }
        }
    }
})

const timeAgo = new TimeAgo('es');

window.onload = function () {
    inicializarTweets(document);
    inicializarScrollInfinito();
}

// Prepara los tweets que haya dentro de root: fechas relativas, like y tooltip.
// Se llama al cargar la página y con cada bloque nuevo del scroll infinito.
function inicializarTweets(root) {
    const nodes = root.querySelectorAll('span.timeago');
    nodes.forEach(node => {
        node.textContent = timeAgo.format(new Date(node.textContent.trim()));
    });

    // Agregar evento de clic a los enlaces de "Like"
    root.querySelectorAll('.tweet-like > a').forEach(enlace => {
    enlace.addEventListener('click', event => {
        // Evitamos que el enlace recargue la página
        event.preventDefault();
        const href = enlace.getAttribute('href');
        fetch(href)
            .then(response => {
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.json();
            })
            .then(data => {
                let tweetLikeCount = enlace.querySelector('.tweet-like-count');
                if (tweetLikeCount) {
                    tweetLikeCount.textContent = data.numLikes;
                }
                // La lista de quién ha dado like ha cambiado: recargar el tooltip
                delete enlace.dataset.loaded;
                if (enlace.matches(':hover, :focus')) {
                    mostrarTooltipLikes(enlace);
                }
            })
            .catch(error => {
                console.error('Error al hacer like:', error);
            });
    });
    ;
});

    // Tooltip con los avatares de quién ha dado like
    root.querySelectorAll('.tweet-like > a[data-wholikes]').forEach(enlace => {
        const li = enlace.closest('.tweet-like');
        let ocultarTimer;
        const mostrar = () => { clearTimeout(ocultarTimer); mostrarTooltipLikes(enlace); };
        const ocultar = () => {
            ocultarTimer = setTimeout(() => { li.querySelector('.likes-tooltip').hidden = true; }, 150);
        };
        enlace.addEventListener('mouseenter', mostrar);
        enlace.addEventListener('focus', mostrar);
        const tooltip = li.querySelector('.likes-tooltip');
        // Mantener abierto el tooltip mientras el ratón o el foco estén sobre él (para poder hacer clic)
        tooltip.addEventListener('mouseenter', () => clearTimeout(ocultarTimer));
        tooltip.addEventListener('focusin', () => clearTimeout(ocultarTimer));
        tooltip.addEventListener('focusout', ocultar);
        li.addEventListener('mouseleave', ocultar);
        enlace.addEventListener('blur', ocultar);
        enlace.addEventListener('keydown', e => { if (e.key === 'Escape') li.querySelector('.likes-tooltip').hidden = true; });
    });
}

// Scroll infinito: cuando el centinela del final entra en pantalla se pide la página siguiente
function inicializarScrollInfinito() {
    const feed = document.querySelector('.feed');
    if (!feed || !('IntersectionObserver' in window)) return; // sin soporte queda el enlace "Cargar más"

    let cargando = false;
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) cargarSiguientePagina(entry.target);
        });
    }, { rootMargin: '400px 0px' }); // empezar a cargar un poco antes de llegar al final

    function observarCentinela() {
        const centinela = feed.querySelector('.feed-sentinel');
        if (centinela) observer.observe(centinela);
    }

    function cargarSiguientePagina(centinela) {
        if (cargando) return;
        cargando = true;
        observer.unobserve(centinela);
        centinela.classList.add('is-loading');

        fetch(centinela.dataset.next, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => {
                if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
                return response.text();
            })
            .then(html => {
                const plantilla = document.createElement('template');
                plantilla.innerHTML = html;
                const nuevos = plantilla.content;
                // Escalonar la animación de entrada solo entre los tweets nuevos
                nuevos.querySelectorAll('.tweet').forEach((tweet, i) => {
                    tweet.style.animationDelay = `${Math.min(i, 5) * 80}ms`;
                });
                inicializarTweets(nuevos);
                centinela.replaceWith(nuevos);
                observarCentinela();
            })
            .catch(error => {
                console.error('Error al cargar más tweets:', error);
                centinela.classList.remove('is-loading');
                // Si falla, el enlace "Cargar más" sigue ahí para reintentar a mano
                setTimeout(() => observer.observe(centinela), 3000);
            })
            .finally(() => { cargando = false; });
    }

    observarCentinela();
}

function mostrarTooltipLikes(enlace) {
    const tooltip = enlace.closest('.tweet-like').querySelector('.likes-tooltip');
    tooltip.hidden = false;
    if (enlace.dataset.loaded) return;
    enlace.dataset.loaded = '1';
    tooltip.innerHTML = '<span class="likes-tooltip-empty">Cargando…</span>';

    fetch(enlace.dataset.wholikes, { headers: { 'Accept': 'application/json' } })
        .then(response => {
            const tipo = response.headers.get('Content-Type') || '';
            // Si no hay sesión, Symfony redirige al login (HTML en vez de JSON)
            if (response.redirected || response.status === 401 || response.status === 403) {
                throw new Error('login');
            }
            if (!response.ok || !tipo.includes('application/json')) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(usuarios => pintarTooltipLikes(tooltip, usuarios, enlace.dataset.wholikesList))
        .catch(error => {
            delete enlace.dataset.loaded;
            const mensaje = error.message === 'login'
                ? 'Inicia sesión para ver los likes'
                : 'No se han podido cargar los likes';
            if (error.message !== 'login') console.error('Error al cargar los likes:', error);
            tooltip.innerHTML = `<span class="likes-tooltip-empty">${mensaje}</span>`;
        });
}

function pintarTooltipLikes(tooltip, usuarios, urlLista) {
    tooltip.replaceChildren();
    if (usuarios.length === 0) {
        tooltip.innerHTML = '<span class="likes-tooltip-empty">Nadie ha dado like todavía</span>';
        return;
    }
    const MAX = 8;
    const lista = document.createElement('ul');
    lista.className = 'likes-tooltip-list';
    usuarios.slice(0, MAX).forEach(u => {
        const item = document.createElement('li');
        item.title = '@' + u.username;
        let avatar;
        if (u.avatar) {
            avatar = document.createElement('img');
            avatar.src = u.avatar;
            avatar.alt = '@' + u.username;
        } else {
            avatar = document.createElement('span');
            avatar.textContent = u.username.charAt(0);
            avatar.setAttribute('aria-label', '@' + u.username);
        }
        avatar.className = 'likes-tooltip-avatar';
        item.appendChild(avatar);
        lista.appendChild(item);
    });

    const texto = document.createElement('p');
    texto.className = 'likes-tooltip-text';
    const nombres = usuarios.slice(0, 2).map(u => '@' + u.username).join(', ');
    const resto = usuarios.length - 2;
    texto.textContent = resto > 0 ? `${nombres} y ${resto} más` : nombres;

    // Todo el tooltip es un enlace a la página con la lista completa
    const enlaceLista = document.createElement('a');
    enlaceLista.className = 'likes-tooltip-link';
    enlaceLista.href = urlLista;
    enlaceLista.setAttribute('aria-label', `Ver los ${usuarios.length} usuarios a los que les gusta`);
    enlaceLista.append(lista, texto);
    tooltip.appendChild(enlaceLista);
}
