# SudoWP Hooks Visualizer

**Contributors:** SudoWP, WP Republic  
**Original Authors:** Stuart O'Brien, cxThemes  
**Tags:** hooks, actions, filters, developer-tool, debug, security-fork  
**Requires at least:** 5.8  
**Tested up to:** 6.7  
**Stable tag:** 1.3.0  
**License:** GPLv2 or later  

## Security Notice
This is a **community-maintained fork** of the abandoned "Simply Show Hooks" plugin. The original plugin is no longer maintained and may contain security vulnerabilities.

**This version introduces strict input sanitization and code modernization** to ensure it is safe for use in production and staging environments.

---

## Description

**SudoWP Hooks Visualizer** is a developer tool that helps you see where all the action and filter hooks are firing on any WordPress page. It is a secure, modernized version of the classic "Simply Show Hooks".

**Key Features:**
* **Visual Hook Map:** Displays hooks directly on the page where they trigger.
* **Deep Inspection:** See attached functions, their priority, and accepted arguments.
* **Security Hardened:** Input validation prevents XSS vectors found in legacy debugging tools.
* **One-Click Toggle:** Enable or disable globally via the Admin Bar.

## Installation

1.  Download the plugin zip file (or clone this repo).
2.  **Important:** Deactivate and delete the original "Simply Show Hooks" plugin if installed.
3.  Upload the `sudowp-hooks-visualizer` folder to your `/wp-content/plugins/` directory.
4.  Activate the plugin through the 'Plugins' menu in WordPress.
5.  Look for the **"SudoWP Hooks"** menu in your Admin Bar.

## Frequently Asked Questions

**Why did you fork this?** The original tool was incredibly useful but abandoned. We rely on it for debugging, so we patched it to ensure it remains safe and compatible with newer PHP versions.

**Is it safe to keep active?** While we have hardened the security, this is primarily a **debugging tool**. We recommend activating it only when you are actively developing or troubleshooting a site, and keeping it inactive otherwise.

## Changelog

### Version 1.3.0 (SudoWP Edition)
* **Security Fix:** Implemented strict sanitization for all user inputs (`$_GET`, `$_COOKIE`).
* **Security Fix:** Hardened cookie setting with secure flags.
* **Maintenance:** Refactored codebase to use `SudoWP_` namespace and prevent conflicts.
* **Rebrand:** Forked as SudoWP Hooks Visualizer.
