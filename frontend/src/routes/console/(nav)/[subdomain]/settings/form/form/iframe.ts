export function resizeIframe(iframe: HTMLIFrameElement | undefined, add = 0) {
  if (!iframe) return;
  iframe.style.height =
    iframe.contentWindow!.document.body.scrollHeight + add + "px";
}
