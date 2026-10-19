<?php

namespace App\Enums;

enum InviteTerminatedState: string
{
    case Accepted = 'accepted';
    case AcceptedElsewhere = 'accepted_elsewhere';
    case Canceled = 'canceled';
    case Declined = 'declined';
    case DeclinedElsewhere = 'declined_elsewhere';
    case Error = 'error';

    public static function values(): array
    {
        return array_column(InviteTerminatedState::cases(), 'value');
    }

    public function icon(): string
    {
        return match ($this) {
            self::Accepted, self::AcceptedElsewhere => 'phone',
            self::Canceled => 'phone-x',
            self::Declined, self::DeclinedElsewhere => 'phone-disconnect',
            self::Error => 'phone-slash',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Accepted, self::AcceptedElsewhere => __('Accepted'),
            self::Canceled => __('Canceled'),
            self::Declined, self::DeclinedElsewhere => __('Declined'),
            self::Error => __('Error'),
        };
    }

    public function cssClass(): string
    {
        return match ($this) {
            self::Accepted, self::AcceptedElsewhere => 'color green',
            self::Canceled => 'color orange',
            self::Error => 'color red',
            default => ''
        };
    }
}
