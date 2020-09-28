<?php declare(strict_types=1);


namespace InvExportLabel\Helper;


class Dom
{
    /**
     * @param array $htmlStrings
     * @param string $joinWith
     * @return string
     */
    public static function mergeHtmlStrings(
        array $htmlStrings,
        string $joinWith = '<div class="page_break"></div>'
    ): string {

        if (empty($htmlStrings)) {
            return '';
        }

        $bodyContents = [];

        // we are using plain regex to ensure that DomDocument would not change anything in the HTML string
        foreach ($htmlStrings as $htmlString) {
            $hasBody = preg_match("/<body>(.*)<\/body>/s", $htmlString, $matches);
            if (!$hasBody) {
                throw new \RuntimeException('There seems to be no valid html given for document string');
            }

            $bodyContents[] = $matches[1];
        }

        $domSkeleton = preg_replace("/\<body\>(.*)<\/body>/s", '{{body}}', $htmlStrings[0]);

        return str_replace('{{body}}', join($joinWith, $bodyContents), $domSkeleton);

    }
}
