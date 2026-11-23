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
        year: {
            past: {
                one: "hace {0} año",
                other: "hace {0} años"
            }
        },
    }
})

window.onload = function () {
    const timeAgo = new TimeAgo('es');
    const nodes = document.querySelectorAll('span.timeago');
    nodes.forEach(node => {
        node.textContent = timeAgo.format(new Date(node.textContent));
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
                console.log(data.numLikes);
                let tweetLikeCount = enlace.querySelector('.tweet-like-count');
                if (tweetLikeCount) {
                    tweetLikeCount.textContent = data.numLikes;
                }
            })
            .catch(error => {
                console.error('Error al hacer like:', error);
            });
    });
    ;
});
}
