/**
 * @package koko-analytics
 * @author Danny van Kooten
 * @license GPL-3.0+
 *
 * JavaScript file for initiating the pageview tracking request.
 * Do not use ES2015 features as this file is only intended to be minified, not transpiled.
 */
const trackerUrl=window.koko_analytics.tracker_url,postId=String(parseInt(window.koko_analytics.post_id)),useCookie=Boolean(parseInt(window.koko_analytics.use_cookie));function stringifyObject(e){return Object.keys(e).map((function(o){const t=e[o];return window.encodeURIComponent(o)+"="+window.encodeURIComponent(t)})).join("&")}function getCookie(e){const o=document.cookie?document.cookie.split("; "):[];let t,n;for(let i=0;i<o.length;i++)if(t=o[i].split("="),window.decodeURIComponent(t[0])===e)return n=t.slice(1).join("="),window.decodeURIComponent(n);return""}function setCookie(e,o,t){let n=(e=window.encodeURIComponent(e))+"="+(o=window.encodeURIComponent(String(o)));t.path&&(n+=";path="+t.path),t.expires&&(n+=";expires="+t.expires.toUTCString()),document.cookie=n}function trackPageview(){if("doNotTrack"in navigator&&"1"===navigator.doNotTrack)return;if("visibilityState"in document&&"prerender"===document.visibilityState)return;if(/bot|crawler|spider|crawling/i.test(navigator.userAgent))return;if(null===document.body)return void document.addEventListener("DOMContentLoaded",trackPageview);const e=getCookie("_koko_analytics_pages_viewed");let o=0===e.length;const t=e.split(",").filter((function(e){return""!==e}));let n=-1===t.indexOf(postId),i="";"string"==typeof document.referrer&&""!==document.referrer&&(0===document.referrer.indexOf(window.location.origin)?(o=!1,document.referrer===window.location.href&&(n=!1)):i=document.referrer);const r=document.createElement("img");function d(){if(r.src="",r.parentNode&&document.body.removeChild(r),useCookie){-1===t.indexOf(postId)&&t.push(postId);const e=new Date;e.setHours(e.getHours()+6),setCookie("_koko_analytics_pages_viewed",t.join(","),{expires:e,path:"/"})}}r.alt="",r.style.display="none",r.setAttribute("aria-hidden","true"),r.onload=d,window.setTimeout(d,5e3);const c={p:postId,nv:o?1:0,up:n?1:0,r:i};r.src=trackerUrl+(trackerUrl.indexOf("?")>-1?"&":"?")+stringifyObject(c),document.body.appendChild(r)}trackPageview();