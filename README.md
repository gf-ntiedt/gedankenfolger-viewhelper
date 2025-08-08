# Gedankenfolger Viewhelper<br/>gedankenfolger-viewhelper

A collection of viewhelpers to make the work a little bit easier.

### IpViewHelper:
ViewHelper to determine and output the client IP address.
This ViewHelper checks in the following order:
*   1. HTTP_CLIENT_IP (shared internet)
*   2. HTTP_X_FORWARDED_FOR (proxy)
*   3. REMOTE_ADDR (direct connection)

and returns the first found IP address as a string.

Example usage:
```
{gfv:ip()}
```
or
```
{gfv:ip()}
```
---

## Namespace cloudflare:<br>```{gfv:cloudflare.viewhelpername()}```

---
### StreamIframeViewHelper:
Generates an ```<iframe>``` element to embed a Cloudflare Stream video.
Constructs the iframe src URL by concatenating:
- streamid (Cloudflare Stream video ID)
- customerid (Cloudflare customer/account ID)
- optional parameters: preload, loop, muted, autoplay


  Example usage:
```
<cf:streamIframe streamid="abc123" customerid="42" preload="auto" loop="true" muted="false" autoplay="true" />
```
---

## Namespace resource:<br>```{gfv:resource.viewhelpername()}```

---
### SvgInlineViewHelper:
Renders an SVG file inline by embedding its XML content directly into the output.
It handles loading the file via FAL (File or FileReference) or by path, validates
that the file is non-empty and an SVG, then parses and injects attributes safely.
Pass additional data-attributes or arbitrary attributes via `data` and `additionalAttributes` arguments.


Example usage:
```
 <gfv:resource.svgInline src="EXT:Sitepackage/Resources/Public/Logo.svg" width="200" />
```
or
```
 <gfv:resource.svgInline image="{fileReference}" class="icon" id="logo" viewBox="0 0 100 100" />
```
