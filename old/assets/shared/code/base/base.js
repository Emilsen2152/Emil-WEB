export const base = (location.hostname === "localhost")
    ? "/"
    : `${location.origin}/${location.pathname.split('/').filter(p => p)[0]}/`;