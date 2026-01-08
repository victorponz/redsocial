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
}
