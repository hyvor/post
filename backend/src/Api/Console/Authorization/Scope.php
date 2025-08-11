<?php

namespace App\Api\Console\Authorization;

enum Scope: string
{

    case NEWSLETTER_READ = 'newsletter.read';
    case NEWSLETTER_WRITE = 'newsletter.write';

    case ISSUE_READ = 'issue.read';
    case ISSUE_WRITE = 'issue.write';

    case SENDING_PROFILE_READ = 'sending_profile.read';
    case SENDING_PROFILE_WRITE = 'sending_profile.write';

    case SUBSCRIBER_READ = 'subscriber.read';
    case SUBSCRIBER_WRITE = 'subscriber.write';

    case USER_READ = 'user.read';
    case USER_WRITE = 'user.write';

    case TEMPLATE_READ = 'template.read';
    case TEMPLATE_WRITE = 'template.write';

    case API_KEYS_READ = 'api_keys.read';
    case API_KEYS_WRITE = 'api_keys.write';

    case MEDIA_WRITE = 'media.write';

    case DATA_READ = 'data.read';
    case DATA_WRITE = 'data.write';
}
