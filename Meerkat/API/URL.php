<?php

namespace Statamic\Addons\Meerkat\API;

use Statamic\API\URL as StatamicUrlAPI;

class URL extends StatamicUrlAPI
{

   /**
    * Make sure the site root is prepended to a URL
    *
    * @param  string       $url
    * @param  string|null  $locale
    * @param  boolean      $controller
    * @return string
    */
   public static function prependSiteRoot($url, $locale = null, $controller = true)
   {
       // Backwards compatibility fix:
       // 2.1 added the $locale argument in the second position to match prependSiteurl.
       // Before 2.1, the second argument was controller. We'll handle that here.
       if ($locale === true || $locale === false) {
           $controller = $locale;
           $locale = null;
       }

       return StatamicUrlAPI::makeRelative(
           StatamicUrlAPI::prependSiteUrl($url, $locale, $controller)
       );
   }

}