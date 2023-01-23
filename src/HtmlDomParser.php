<?php

namespace PhpSimple;

use Exception;

class HtmlDomParser
{
    /**
     * @throws Exception
     */
    public static function fileGetHtml(
        string $url,
        bool $useIncludePath = false,
        $context = null,
        int $offset = 0,
        bool $lowercase = true,
        bool $forceTagsClosed = true,
        string $targetCharset = Constants::DEFAULT_TARGET_CHARSET,
        bool $stripRN = true,
        string $defaultBRText = Constants::DEFAULT_BR_TEXT,
        string $defaultSpanText = Constants::DEFAULT_SPAN_TEXT
    ): ?SimpleHtmlDom {
        if (!defined('MAX_FILE_SIZE')) {
            define('MAX_FILE_SIZE', 600000);
        }
        // We DO force the tags to be terminated.
        $dom = new SimpleHtmlDom(null, $lowercase, $forceTagsClosed, $targetCharset, $stripRN, $defaultBRText, $defaultSpanText);
        // For sourceforge users: uncomment the next line and comment the retreive_url_contents line 2 lines down if it is not already done.
        $contents = file_get_contents($url, $useIncludePath, $context, $offset);
        // Paperg - use our own mechanism for getting the contents as we want to control the timeout.
        if (empty($contents) || strlen($contents) > MAX_FILE_SIZE) {
            $dom->clear();
            throw new Exception('Content size exceed ' . MAX_FILE_SIZE);
        }
        // The second parameter can force the selectors to all be lowercase.
        $dom->load($contents, $lowercase, $stripRN);

        return $dom;
    }

    /**
     * @throws Exception
     */
    public static function strGetHtml(
        string $str,
        bool $lowercase = true,
        bool $forceTagsClosed = true,
        string $targetCharset = Constants::DEFAULT_TARGET_CHARSET,
        bool $stripRN = true,
        string $defaultBRText = Constants::DEFAULT_BR_TEXT,
        string $defaultSpanText = Constants::DEFAULT_SPAN_TEXT
    ): ?SimpleHtmlDom {
        if (!defined('MAX_FILE_SIZE')) {
            define('MAX_FILE_SIZE', 600000);
        }
        $dom = new SimpleHtmlDom(null, $lowercase, $forceTagsClosed, $targetCharset, $stripRN, $defaultBRText, $defaultSpanText);
        if (empty($str) || strlen($str) > MAX_FILE_SIZE) {
            $dom->clear();
            throw new Exception('Content size exceed ' . MAX_FILE_SIZE);
        }
        $dom->load($str, $lowercase, $stripRN);

        return $dom;
    }
}
