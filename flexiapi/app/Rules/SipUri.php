<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SipUri implements ValidationRule
{
    /**
     * Run the validation rule.
     * Validates a SIP/SIPS URI against RFC 3261 §19.1 ABNF grammar.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match($this->buildPattern(), $value)) {
            $fail('The :attribute must be a valid SIP URI.');
        }
    }

    /**
     * Build the RFC 3261 §19.1 compliant regex pattern.
     * Generated using Claude
     */
    private function buildPattern(): string
    {
        // unreserved = alphanum / mark   (mark = - _ . ! ~ * ' ( ))
        $unreserved     = '[A-Za-z0-9\-_.!~*\'()]';

        // escaped = "%" HEXDIG HEXDIG
        $escaped        = '%[0-9A-Fa-f]{2}';

        // user-unreserved = & = + $ , ; ? /
        $userUnreserved = '[&=+$,;?\/]';

        // user = 1*( unreserved / escaped / user-unreserved )
        $user           = "(?:{$unreserved}|{$escaped}|{$userUnreserved})+";

        // password = *( unreserved / escaped / & = + $ , )
        $password       = "(?:{$unreserved}|{$escaped}|[&=+$,])*";

        // userinfo = user [ ":" password ] "@"
        $userinfo       = "(?:{$user})(?::{$password})?@";

        // domainlabel = alphanum / alphanum *( alphanum / "-" ) alphanum
        $domainLabel    = '[A-Za-z0-9](?:[A-Za-z0-9\-]*[A-Za-z0-9])?';

        // toplabel = ALPHA / ALPHA *( alphanum / "-" ) alphanum
        $topLabel       = '[A-Za-z](?:[A-Za-z0-9\-]*[A-Za-z0-9])?';

        // hostname = *( domainlabel "." ) toplabel [ "." ]
        $hostname       = "(?:{$domainLabel}\.)*{$topLabel}\.?";

        // IPv4address = 1*3DIGIT "." x3
        $ipv4           = '\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}';

        // IPv6 building blocks (RFC 5954)
        $h16            = '[0-9A-Fa-f]{1,4}';
        $ls32           = "(?:{$h16}:{$h16}|{$ipv4})";
        $ipv6           = "(?:"
                        . "(?:{$h16}:){6}{$ls32}"
                        . "|::(?:{$h16}:){5}{$ls32}"
                        . "|{$h16}?::(?:{$h16}:){4}{$ls32}"
                        . "|(?:{$h16}:)?{$h16}::(?:{$h16}:){3}{$ls32}"
                        . "|(?:{$h16}:){0,2}{$h16}::(?:{$h16}:){2}{$ls32}"
                        . "|(?:{$h16}:){0,3}{$h16}::{$h16}:{$ls32}"
                        . "|(?:{$h16}:){0,4}{$h16}::{$ls32}"
                        . "|(?:{$h16}:){0,5}{$h16}::{$h16}"
                        . "|(?:{$h16}:){0,6}{$h16}::"
                        . ")";

        // IPv6reference = "[" IPv6address "]"
        $ipv6ref        = "\[{$ipv6}\]";

        // host = IPv6reference / IPv4address / hostname (order matters)
        $host           = "(?:{$ipv6ref}|{$ipv4}|{$hostname})";

        // hostport = host [ ":" port ]
        $hostport       = "{$host}(?::\d+)?";

        // uri-parameters: *( ";" pname [ "=" pvalue ] )
        $paramChar      = "(?:{$unreserved}|{$escaped}|[\\[\\]\/:&+$])";
        $uriParams      = "(?:;{$paramChar}*(?:={$paramChar}*)?)*";

        // headers: "?" hname "=" hvalue *( "&" hname "=" hvalue )
        $hnvUnreserved  = '[\\[\\]\/?:+$]';
        $headerChar     = "(?:{$unreserved}|{$escaped}|{$hnvUnreserved})";
        $headers        = "(?:\\?{$headerChar}+={$headerChar}*(?:&{$headerChar}+={$headerChar}*)*)?";

        return '/^sips?:(?:' . $userinfo . ')?' . $hostport . $uriParams . $headers . '$/i';
    }
}