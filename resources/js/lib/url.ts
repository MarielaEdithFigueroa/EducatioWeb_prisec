export function getUrlPath(url: string): string {
    return url.split(/[?#]/)[0] || url;
}
