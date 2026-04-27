<?php

declare(strict_types=1);

namespace Webtolk\Max\Payload;

enum SenderAction: string
{
    case TYPING_ON = 'typing_on';
    case SENDING_PHOTO = 'sending_photo';
    case SENDING_VIDEO = 'sending_video';
    case SENDING_AUDIO = 'sending_audio';
    case SENDING_FILE = 'sending_file';
    case MARK_SEEN = 'mark_seen';
}
