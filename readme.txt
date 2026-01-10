=== SudoWP Hooks Visualizer ===
Contributors: SudoWP, WP Republic
Tags: hooks, actions, filters, developer-tool, debug, security-fork
Requires at least: 5.8
Tested up to: 6.7
Stable tag: 1.3.0
License: GPLv2 or later

A secure, modernized fork of the abandoned "Simply Show Hooks". Visualize WordPress hooks in real-time.

== Description ==

> **🛡️ SECURITY NOTICE:**
> This is a **maintained fork** by **SudoWP**.
> The original plugin was removed from the repository due to security issues/abandonment.
> This version has been patched, sanitized, and rebranded to ensure safety for developers.

**SudoWP Hooks Visualizer** helps theme and plugin developers quickly see where all the action and filter hooks are firing on any WordPress page.

**Why use this fork?**
* **Security Hardened:** All inputs (`$_GET`, `$_COOKIE`) are now strictly sanitized to prevent XSS.
* **Malware Free:** Cleaned codebase, free from the vulnerabilities found in the original abandoned version.
* **Modernized:** Updated code standards and conflict prevention.

**Features:**
* Visualize Action Hooks
* Visualize Filter Hooks
* See hook priorities and attached functions
* One-click toggle from the Admin Bar

== Installation ==

1. Download the plugin `.zip` from GitHub.
2. Upload to your WordPress site.
3. Activate "SudoWP Hooks Visualizer".
4. Use the "SudoWP Hooks" menu in the Admin Bar to toggle visualization.

== Changelog ==

= 1.3.0 (SudoWP Edition) =
* SECURITY: Implemented strict sanitization for all user inputs.
* SECURITY: Removed unsafe direct calls and modernized headers.
* REBRAND: Forked as SudoWP Hooks Visualizer.