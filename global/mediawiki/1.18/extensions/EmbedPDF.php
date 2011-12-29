<?php
        /**
         * MediaWiki EmbedPDF extension
         *
         * @version 0.1
         * @author Dmitry Shurupov
         * @link http://www.mediawiki.org/wiki/Extension:EmbedPDF
         */
 
        $wgExtensionCredits['parserhook'][] = array(
                'name' => 'EmbedPDF',
                'author' => 'Dmitry Shurupov',
                'version' => '0.1',
                'url' => 'http://www.mediawiki.org/wiki/Extension:EmbedPDF',
                'description' => 'Allows to embed .pdf documents on a wiki page.',
                );
 
        $wgExtensionFunctions[] = 'registerEmbedPDFHandler';
 
        function registerEmbedPDFHandler ()
        {
                global $wgParser;
                $wgParser->setHook( 'pdf', 'embedPDFHandler' );
        }
 
        function makeHTMLforPDF ( $path, $argv )
        {
                if (empty($argv['width']))
                {
                        $width = '1000';
                }
                else
                {
                        $width = $argv['width'];
                }
 
                if (empty($argv['height']))
                {
                        $height = '700';
                }
                else
                {
                        $height = $argv['height'];
                }
                return '<object data="'.$path.'" width="'.$width.'" height="'.$height.'" type="application/pdf"></object>';
        }
 
        function embedPDFHandler ( $input, $argv )
        {
                if (!$input)
                        return '<font color="red">Error: empty param in &lt;pdf&gt;!</font>';
 
                if (preg_match('/^[^\/]+\.pdf$/i', $input))
                {
                        $img = Image::newFromName( $input );
                        if ($img != NULL)
                                return makeHTMLforPDF( $img->getURL(), $argv );
                }
 
                if (preg_match('/^http\:\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@\?\^\=\%\&:\/\~\+\#]*[\w\-\@\?\^\=\%\&\/\~\+\#])?\.pdf$/i', $input))
                        return makeHTMLforPDF( $input, $argv );
                else
                        return '<font color="red">Error: bad URI in &lt;pdf&gt;!</font>';
        }
?>