=== SudoWP Hooks Visualizer (Security Fork) ===
Contributors: SudoWP, WP Republic
Original Authors: Stuart O'Brien, cxThemes
Tags: hooks, actions, filters, developer-tool, debug, security-fork, cve-2024-6297
Requires at least: 5.8
Tested up to: 6.7
Stable tag: 1.3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A secure, community-maintained fork of "Simply Show Hooks". Guaranteed clean from CVE-2024-6297 and patched for XSS.

== Description ==

This is SudoWP Hooks Visualizer, a community-maintained and security-hardened fork of the "Simply Show Hooks" plugin.

**Security Notice (CVE-2024-6297):**
The original "Simply Show Hooks" plugin was compromised in a major supply chain attack in June 2024 (CVE-2024-6297), where malicious code was injected to create unauthorized admin accounts. The original plugin was permanently closed by WordPress.

**This SudoWP Edition is:**
1. **Clean:** Forked from a verified clean codebase, guaranteed free from the CVE-2024-6297 backdoor.
2. **Patched:** We have additionally fixed legacy Cross-Site Scripting (XSS) vulnerabilities found in the visualization output.

**What does it do?**
It allows developers to visualize WordPress Action and Filter hooks firing on any page, helping to debug and understand the execution flow.

**DISCLAIMER:** This plugin is NOT affiliated with, endorsed by, or associated with the original authors. It is an independent fork maintained by the SudoWP security project.

**Key Features Preserved:**
* Visual Hook Map: See exactly where hooks fire on the front-end.
* Action & Filter Support: Inspect both Actions and Filters.
* Priority Inspection: View attached functions and their execution priority.
* Admin Bar Toggle: Easily switch visualization on/off.

**Security Improvements in SudoWP Edition:**
* **Strict Sanitization:** All inputs (GET/COOKIE) are now rigorously sanitized to prevent XSS attacks.
* **Modernized Codebase:** Refactored deprecated code and improved PHP 8.x compatibility.
* **Secure Cookies:** Implemented secure flags for cookies handling the visualization state.

== Installation ==

1. Upload the `sudowp-hooks-visualizer` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Use the "SudoWP Hooks" menu in the Admin Bar to toggle visualization on/off.

== Frequently Asked Questions ==

= Is this compatible with the original Simply Show Hooks? =
No. This is a standalone fork. You must deactivate and delete the original plugin immediately, especially if you have an older version installed, to avoid the CVE-2024-6297 vulnerability.

= Why use this fork? =
The original plugin is abandoned and compromised. This fork offers the same beloved functionality but with a secure, maintained codebase suitable for modern WordPress sites.

== Changelog ==

= 1.3.0 (SudoWP Edition) =
* Security Fix: Guaranteed clean from CVE-2024-6297 (Supply Chain Attack).
* Security Fix: Implemented strict sanitization for all user inputs (GET/COOKIE) to fix XSS.
* Security Fix: Removed unsafe direct calls and modernized headers.
* Maintenance: Refactored codebase to use `sudowp-` naming convention.
* Rebrand: Forked as SudoWP Hooks Visualizer.