# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.3.2   | :white_check_mark: |
| 1.3.1   | :white_check_mark: |
| 1.3.0   | :white_check_mark: |
| < 1.3.0 | :x:                |

## Security Measures

This plugin implements multiple layers of security following OWASP best practices:

### 1. CVE-2024-6297 Protection
This fork was created specifically to address the supply chain attack (CVE-2024-6297) that compromised the original "Simply Show Hooks" plugin. This version is guaranteed clean and forked from a verified safe codebase.

### 2. OWASP Top 10 Protections

#### A01:2021 - Broken Access Control
- **Administrator-Only Access**: Plugin functionality requires `manage_options` capability
- **Multiple Authorization Checks**: Every output method verifies user capabilities
- **Secure Cookie Handling**: State is managed with secure, HTTP-only, SameSite cookies

#### A02:2021 - Cryptographic Failures
- **Secure Cookie Attributes**: 
  - `httponly: true` - Prevents JavaScript access
  - `secure: true` - HTTPS-only when SSL is enabled
  - `samesite: Lax` - CSRF protection at cookie level

#### A03:2021 - Injection
- **Input Validation**: All inputs are validated and sanitized
- **Hook Name Validation**: Strict validation prevents injection attacks
- **Type Checking**: Strict type hints throughout the codebase
- **Output Escaping**: All output uses `esc_html()`, `esc_attr()`, and `esc_url()`

#### A04:2021 - Insecure Design
- **Secure by Default**: Plugin is off by default
- **Defense in Depth**: Multiple security layers
- **Fail Secure**: Invalid inputs result in safe defaults

#### A05:2021 - Security Misconfiguration
- **Direct File Access Prevention**: Checks for `ABSPATH` constant
- **Security Headers**: X-Content-Type-Options header prevents MIME sniffing
- **Proper Error Handling**: No sensitive information in error messages

#### A07:2021 - Cross-Site Scripting (XSS)
- **Comprehensive Output Escaping**: All dynamic content is escaped
- **Context-Aware Escaping**: Uses appropriate escaping functions for HTML, attributes, and URLs
- **Nonce Verification**: Prevents unauthorized state changes

#### A08:2021 - Cross-Site Request Forgery (CSRF)
- **WordPress Nonces**: All state-changing operations verify nonces
- **Action-Specific Nonces**: Each action has its own nonce
- **Proper Nonce Validation**: Uses `wp_verify_nonce()` with sanitized input

### 3. Additional Security Features

#### Strict Type Safety
- PHP 8.2 compatible with strict type declarations
- Type hints on all method parameters and return values

#### Rate Limiting
- Recent hooks limited to 100 entries to prevent memory exhaustion

#### Cookie Security
- Domain validation with fallback handling
- 30-day expiration
- Path-scoped to root

#### Input Sanitization
- All `$_REQUEST` data sanitized using WordPress functions
- `sanitize_key()` for status values
- `sanitize_text_field()` for nonces
- Type validation before processing

## Reporting a Vulnerability

If you discover a security vulnerability in SudoWP Hooks Visualizer, please report it by:

1. **DO NOT** open a public GitHub issue
2. Email security concerns to: security@sudowp.com (if available) or create a private security advisory on GitHub
3. Include:
   - Description of the vulnerability
   - Steps to reproduce
   - Potential impact
   - Suggested fix (if any)

### What to Expect

- **Acknowledgment**: Within 48 hours
- **Initial Assessment**: Within 1 week
- **Fix Timeline**: Critical issues within 7 days, others within 30 days
- **Public Disclosure**: After fix is released and users have time to update

## Security Best Practices for Users

1. **Only Enable When Needed**: This is a debugging tool - keep it deactivated in production
2. **Admin Only**: Never give non-admin users access to this plugin
3. **Keep Updated**: Always use the latest version
4. **HTTPS Only**: Use SSL/TLS for all admin operations
5. **Regular Audits**: Periodically review who has admin access

## Known Limitations

1. **Not for Production Use**: This is a developer debugging tool
2. **Performance Impact**: Tracking all hooks has performance overhead
3. **Memory Usage**: Can increase memory usage on hook-heavy sites

## Security Audit History

### Version 1.3.2 (February 2026)
- Added CSRF protection with WordPress nonces
- Enhanced authorization checks on all rendering methods
- Improved input validation with type checking
- Added security headers (X-Content-Type-Options)
- Fixed COOKIE_DOMAIN handling for undefined constant
- Enhanced direct file access prevention

### Version 1.3.0 (Original Security Fork)
- Forked from clean codebase (pre-CVE-2024-6297)
- Initial XSS vulnerability fixes
- Added secure cookie handling
- Implemented administrator-only access controls

## References

- [OWASP Top 10 2021](https://owasp.org/Top10/)
- [WordPress Plugin Security Best Practices](https://developer.wordpress.org/plugins/security/)
- [CVE-2024-6297 Details](https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2024-6297)
- [WordPress Nonce Documentation](https://developer.wordpress.org/plugins/security/nonces/)

## License

This security policy is part of the SudoWP Hooks Visualizer plugin and is licensed under GPLv2 or later.
