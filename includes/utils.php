<?php
function td_sanitize_XSS($input, $allowHtml = true) {
    if (empty($input)) {
        return '';
    }

    if (!$allowHtml) {
        // If HTML is not allowed, just escape everything
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // Remove dangerous tags completely
    $dangerousTags = [
        'script', 'object', 'embed', 'applet', 'meta', 'link',
        'style', 'iframe', 'frame', 'frameset', 'form', 'input',
        'button', 'textarea', 'select', 'option'
    ];

    $pattern = '/<\s*(' . implode('|', $dangerousTags) . ')\b[^>]*>.*?<\s*\/\s*\1\s*>/is';
    $input = preg_replace($pattern, '', $input);

    // Remove self-closing dangerous tags
    $pattern = '/<\s*(' . implode('|', $dangerousTags) . ')\b[^>]*\/?>/i';
    $input = preg_replace($pattern, '', $input);

    // Remove all event handlers (onclick, onload, etc.)
    $input = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\'][^>]*/i', '', $input);
    $input = preg_replace('/\s*on\w+\s*=\s*[^>\s]+[^>]*/i', '', $input);

    // Remove javascript: protocol from href and src
    $input = preg_replace('/href\s*=\s*["\']?\s*javascript\s*:[^"\'>\s]*/i', 'href="#"', $input);
    $input = preg_replace('/src\s*=\s*["\']?\s*javascript\s*:[^"\'>\s]*/i', 'src=""', $input);

    // Remove data: protocol (can be used for XSS)
    $input = preg_replace('/href\s*=\s*["\']?\s*data\s*:[^"\'>\s]*/i', 'href="#"', $input);
    $input = preg_replace('/src\s*=\s*["\']?\s*data\s*:[^"\'>\s]*/i', 'src=""', $input);

    // Remove vbscript: protocol
    $input = preg_replace('/href\s*=\s*["\']?\s*vbscript\s*:[^"\'>\s]*/i', 'href="#"', $input);
    $input = preg_replace('/src\s*=\s*["\']?\s*vbscript\s*:[^"\'>\s]*/i', 'src=""', $input);

    // Remove style attributes that might contain expressions
    $input = preg_replace('/style\s*=\s*["\'][^"\']*expression\s*\([^"\']*["\'][^>]*/i', '', $input);
    $input = preg_replace('/style\s*=\s*["\'][^"\']*javascript\s*:[^"\']*["\'][^>]*/i', '', $input);

    // Remove XML processing instructions
    $input = preg_replace('/<\?.*?\?>/s', '', $input);

    // Remove comments that might contain scripts
    $input = preg_replace('/<!--.*?-->/s', '', $input);

    // Remove CDATA sections
    $input = preg_replace('/<!\[CDATA\[.*?\]\]>/s', '', $input);

    // Clean up any remaining dangerous attributes
    $dangerousAttrs = [
        'formaction', 'autofocus', 'background', 'dynsrc', 'lowsrc'
    ];

    foreach ($dangerousAttrs as $attr) {
        $input = preg_replace('/\s*' . $attr . '\s*=\s*["\'][^"\']*["\'][^>]*/i', '', $input);
    }

    return trim($input);
}