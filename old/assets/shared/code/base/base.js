export const base = (location.hostname === "localhost")
    ? "/old/"
    : `${location.origin}/${location.pathname.split('/').filter(p => p)[0]}/old/`;