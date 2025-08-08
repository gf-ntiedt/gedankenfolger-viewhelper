<h1>TYPO3 Extension Gedankenfolger Viewhelper<br/>(gedankenfolger-viewhelper)</h1>
<p>
    A collection of viewhelpers to make the work a little bit easier.
</p>
<p>
    First of all many thanks to the hole TYPO3 community, all supporters of TYPO3 and especially to the <a href="https://typo3.org/" target="_blank">TYPO3-Team</a> + <a href="https://www.gedankenfolger.de/" target="_blank">Gedankenfolger GmbH</a>.
</p>

<h3>
    Contents of this file
</h3>
<ol>
    <li>
        <a href="#viewhelper">Viewhelper</a>
        <ol>
            <li>
                <a href="#ipviewhelper">IpViewHelper</a>
            </li>
            <li>
                <a href="#streamiframeviewHelper">StreamIframeViewHelper</a>
            </li>
            <li>
                <a href="#svginlineviewhelper">SvgInlineViewHelper</a>
            </li>
            <li>
                <a href="#urlschemeviewhelper">UrlschemeViewHelper</a>
            </li>
        </ol>
    </li>
</ol>
<hr/>
<h3 id="viewhelper">
    Viewhelper:
</h3>

<h3 id="ipviewhelper">
    IpViewHelper
</h3>

<p>
    ViewHelper to determine and output the client IP address.<br/>
    This ViewHelper checks in the following order:
</p>

<ol>
    <li>
        HTTP_CLIENT_IP (shared internet)
    </li>
    <li>
        HTTP_X_FORWARDED_FOR (proxy)
    </li>
    <li>
        REMOTE_ADDR (direct connection)
    </li>
</ol>
<p>
    Example usage:
</p>

```xml
<gfv:ip>
```

<hr/>

<h3 id="namespace_cloudflare">
    Namespace cloudflare: <br/> <code>{gfv:cloudflare}</code>
</h3>

<h3 id="streamiframeviewHelper">
    StreamIframeViewHelper
</h3>

<p>
    Generates an iframe tag to embed a Cloudflare Stream video.<br/>
    Constructs the iframe src URL by concatenating:
</p>

<ol>
    <li>
        streamid (Cloudflare Stream video ID)
    </li>
    <li>
        customerid (Cloudflare customer/account ID)
    </li>
    <li>
        optional parameters: preload, loop, muted, autoplay
    </li>
</ol>

<p>
    Example usage:
</p>

```xml
<gfv:streamIframe streamid="abc123" customerid="42" preload="auto" loop="true" muted="false" autoplay="true" />
```

<hr/>

<h3 id="namespace_resource">
    Namespace resource: <br/> <code>{gfv:resource}</code>
</h3>

<h3 id="svginlineviewhelper">
    SvgInlineViewHelper
</h3>

<p>
    Renders an SVG file inline by embedding its XML content directly into the output.<br/>
    It handles loading the file via FAL (File or FileReference) or by path, validates that the file is non-empty and an SVG, then parses and injects attributes safely.<br/>
    Pass additional data-attributes or arbitrary attributes via `data` and `additionalAttributes` arguments.
</p>

<ol>
    <li>
        streamid (Cloudflare Stream video ID)
    </li>
    <li>
        customerid (Cloudflare customer/account ID)
    </li>
    <li>
        optional parameters: preload, loop, muted, autoplay
    </li>
</ol>
<p>
    Example usage:
</p>

```xml
<gfv:resource.svgInline src="EXT:Sitepackage/Resources/Public/Logo.svg" width="200" />
```

or

```xml
<gfv:resource.svgInline image="{fileReference}" class="icon" id="logo" viewBox="0 0 100 100" />
```

<hr/>

<h3 id="namespace_link">
    Namespace link: <br/> <code>{gfv:link}</code>
</h3>

<h3 id="urlschemeviewhelper">
    UrlschemeViewHelper
</h3>

<p>
    This ViewHelper generates a hyperlink for phone numbers by formatting them according to a specific scheme (e.g., tel:). <br/>
    It validates the phone number format and ensures it is properly formatted before creating the link.
</p>

<ol>
    <li>
        streamid (Cloudflare Stream video ID)
    </li>
    <li>
        customerid (Cloudflare customer/account ID)
    </li>
    <li>
        optional parameters: preload, loop, muted, autoplay
    </li>
</ol>
<p>
    Example usage:
</p>

```xml
<gfv:link.urlscheme number="+49 (0) 7777 77 77 77" />
```

<hr/>
