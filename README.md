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
            <li>
                <a href="#packageinfoviewhelper">PackageInfoViewHelper</a>
            </li>
        </ol>
    </li>
    <li>
        <a href="#noticetrademark">Notice on Logo / Trademark Use</a>
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

<ul>
    <li>
        streamid:<br>
        Cloudflare Stream video ID
    </li>
    <li>
        customerid:<br>
        Cloudflare customer/account ID
    </li>
</ul>
<p>
    Optional parameters:
</p>
<ul>
    <li>
        preload:<br>
        Video preload mode<br>
        Default:'none'
    </li>
    <li>
        loop:<br>
        Loop video after end<br>
        Default:false
    </li>
    <li>
        muted:<br>
        Start muted<br>
        Default:false
    </li>
    <li>
        autoplay:<br>
        Autoplay video<br>
        Default:null
    </li>
    <li>
        poster:<br>
        Poster timestamp in seconds<br>
        Default:null
    </li>
</ul>

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

<ul>
    <li>
        number:<br>
        Phone number to be formatted
    </li>
</ul>
<p>
    Optional parameters:
</p>
<ul>
    <li>
        scheme:<br>
        URL scheme to prepend, e.g., tel:<br>
        Default: tel:
    </li>
</ul>
<p>
    Example usage:
</p>

```xml
<gfv:link.urlscheme number="+49 (0) 7777 77 77 77" />
```

<hr/>

<h3 id="namespace_composer">
    Namespace composer: <br/> <code>{gfv:composer}</code>
</h3>

<h3 id="packageinfoviewhelper">
    PackageInfoViewHelper
</h3>

<p>
    This ViewHelper exposes package metadata as reported by Composer\InstalledVersions
</p>

<ul>
    <li>
        name:<br>
        Composer package name ("vendor/package") or ext key if heuristicResolve=1.
    </li>
</ul>
<p>
    Optional parameters:
</p>
<ul>
    <li>
        key:<br>
        Optional key to return: packageName|isInstalled|version|prettyVersion|reference|installPath.<br>
        Default:''
    </li>
    <li>
        jsonEncode:<br>
        Return JSON-encoded result (useful for arrays).<br>
        Default:false
    </li>
    <li>
        heuristicResolve:<br>
        Resolve ext key to package by suffix match if no slash is present.<br>
        Default:true
    </li>
    <li>
        exposeInstallPath:<br>
        Include absolute install path (can be sensitive).<br>
        Default:false
    </li>
</ul>
<p>
    Example usage:
</p>

```xml
<gf:composer.packageInfo name="vendor/package" key="prettyVersion" />
```

<hr/>


<h3 id="noticetrademark">
    Notice on Logo / Trademark Use
</h3>
<p>
The logo used in this extension is protected by copyright and, where applicable, trademark law and remains the exclusive property of Gedankenfolger GmbH.

Use of the logo is only permitted in the form provided here. Any changes, modifications, or adaptations of the logo, as well as its use in other projects, applications, or contexts, require the prior written consent of Gedankenfolger GmbH.

In forks, derivatives, or further developments of this extension, the logo may only be used if explicit consent has been granted by Gedankenfolger GmbH. Otherwise, the logo must be removed or replaced with an own, non-protected logo.

All other logos and icons bundled with this extension are either subject to the TYPO3 licensing terms (The MIT License (MIT), see https://typo3.org) or are in the public domain.
</p>