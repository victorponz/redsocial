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

window.onload = function () {
    const timeAgo = new TimeAgo('es');
    const nodes = document.querySelectorAll('span.timeago');
    nodes.forEach(node => {
        node.textContent = timeAgo.format(new Date(node.textContent.trim()));
    });

    // Agregar evento de clic a los enlaces de "Like"
    document.querySelectorAll('.tweet-like a').forEach(enlace => {
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
    document.querySelectorAll('.tweet-like a[data-wholikes]').forEach(enlace => {
        const li = enlace.closest('.tweet-like');
        let ocultarTimer;
        const mostrar = () => { clearTimeout(ocultarTimer); mostrarTooltipLikes(enlace); };
        const ocultar = () => {
            ocultarTimer = setTimeout(() => { li.querySelector('.likes-tooltip').hidden = true; }, 150);
        };
        enlace.addEventListener('mouseenter', mostrar);
        enlace.addEventListener('focus', mostrar);
        li.addEventListener('mouseleave', ocultar);
        enlace.addEventListener('blur', ocultar);
        enlace.addEventListener('keydown', e => { if (e.key === 'Escape') li.querySelector('.likes-tooltip').hidden = true; });
    });
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
        .then(usuarios => pintarTooltipLikes(tooltip, usuarios))
        .catch(error => {
            delete enlace.dataset.loaded;
            const mensaje = error.message === 'login'
                ? 'Inicia sesión para ver los likes'
                : 'No se han podido cargar los likes';
            if (error.message !== 'login') console.error('Error al cargar los likes:', error);
            tooltip.innerHTML = `<span class="likes-tooltip-empty">${mensaje}</span>`;
        });
}

function pintarTooltipLikes(tooltip, usuarios) {
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
    tooltip.appendChild(lista);

    const texto = document.createElement('p');
    texto.className = 'likes-tooltip-text';
    const nombres = usuarios.slice(0, 2).map(u => '@' + u.username).join(', ');
    const resto = usuarios.length - 2;
    texto.textContent = resto > 0 ? `${nombres} y ${resto} más` : nombres;
    tooltip.appendChild(texto);
}
