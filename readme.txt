=== SudoWP Hooks Visualizer (Security Fork) ===
Contributors: SudoWP, WP Republic
Original Authors: Stuart O'Brien, cxThemes
Tags: hooks, actions, filters, developer-tool, debug, security-fork, xss-fix
Requires at least: 5.8
Tested up to: 6.7
Stable tag: 1.3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A secure, community-maintained fork of "Simply Show Hooks". Visualize WordPress Actions & Filters in real-time.

== Description ==

This is **SudoWP Hooks Visualizer**, a community-maintained and security-hardened fork of the abandoned "Simply Show Hooks" plugin.

It allows developers to visualize WordPress Action and Filter hooks firing on any page, helping to debug and understand the execution flow. This version patches critical security vulnerabilities and sanitizes all inputs to prevent Cross-Site Scripting (XSS).

**DISCLAIMER:** This plugin is NOT affiliated with, endorsed by, or associated with the original authors. It is an independent fork maintained by the SudoWP security project.

**Key Features Preserved:**
* **Visual Hook Map:** See exactly where hooks fire on the front-end.
* **Action & Filter Support:** Inspect both Actions and Filters.
* **Priority Inspection:** View attached functions and their execution priority.
* **Admin Bar Toggle:** Easily switch visualization on/off.

**Security Improvements in SudoWP Edition:**
* **Strict Sanitization:** All inputs (`$_GET`, `$_COOKIE`) are now rigorously sanitized to prevent XSS attacks.
* **Modernized Codebase:** Refactored deprecated code and improved PHP 8.x compatibility.
* **Secure Cookies:** Implemented secure flags for cookies handling the visualization state.

== Installation ==

1. Upload the `sudowp-hooks-visualizer` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Use the **"SudoWP Hooks"** menu in the Admin Bar to toggle visualization on/off.

== Frequently Asked Questions ==

= Is this compatible with the original Simply Show Hooks? =
No. This is a standalone fork. You must deactivate and delete the original plugin to avoid conflicts, as this version uses modernized class names (`SudoWP_Hooks_Visualizer`) to ensure stability.

= Why use this fork? =
The original plugin has been abandoned for years and contains unpatched security risks. This fork offers the same beloved functionality but with a secure, maintained codebase suitable for modern WordPress sites.

== Changelog ==

= 1.3.0 (SudoWP Edition) =
* **Security Fix:** Implemented strict sanitization for all user inputs (GET/COOKIE).
* **Security Fix:** Removed unsafe direct calls and modernized headers.
* **Maintenance:** Refactored codebase to use `sudowp-` naming convention.
* **Rebrand:** Forked as SudoWP Hooks Visualizer.
