<?php

namespace PhpSimple;

class HtmlDomParser
{
    public function fileGetHtml(
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
        // We DO force the tags to be terminated.
        $dom = new SimpleHtmlDom(null, $lowercase, $forceTagsClosed, $targetCharset, $stripRN, $defaultBRText, $defaultSpanText);
        // For sourceforge users: uncomment the next line and comment the retreive_url_contents line 2 lines down if it is not already done.
        $contents = file_get_contents($url, $useIncludePath, $context, $offset);
        // Paperg - use our own mechanism for getting the contents as we want to control the timeout.
        if (empty($contents) || strlen($contents) > Constants::MAX_FILE_SIZE) {
            $dom->clear();
            return null;
        }
        // The second parameter can force the selectors to all be lowercase.
        $dom->load($contents, $lowercase, $stripRN);

        return $dom;
    }

    public function strGetHtml(
        string $str,
        bool $lowercase = true,
        bool $forceTagsClosed = true,
        string $targetCharset = Constants::DEFAULT_TARGET_CHARSET,
        bool $stripRN = true,
        string $defaultBRText = Constants::DEFAULT_BR_TEXT,
        string $defaultSpanText = Constants::DEFAULT_SPAN_TEXT
    ): ?SimpleHtmlDom {
        $dom = new SimpleHtmlDom(null, $lowercase, $forceTagsClosed, $targetCharset, $stripRN, $defaultBRText, $defaultSpanText);
        if (empty($str) || strlen($str) > Constants::MAX_FILE_SIZE) {
            $dom->clear();
            return null;
        }
        $dom->load($str, $lowercase, $stripRN);

        return $dom;
    }
}
