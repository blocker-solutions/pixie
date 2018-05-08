<?php

namespace Pixie\Jobs;

/**
 * Class GetAvatar.
 *
 * Retrieve the avatar from the user account.
 */
class GetAvatarURL extends Job
{
    /**
     * Retrieve the account from Steem.
     *
     * @return null|array
     */
    protected function getAccount() : ?array
    {
        // call the steem api.
        $response = $this->steem->call('database_api', 'get_accounts', [[$this->username]]);

        // return null for error responses.
        if ($response->isError()) {
            return null;
        }

        // get the result array.
        $accounts = $response->result();

        // return the first account from the result.
        if (count($accounts) > 0) {
            return array_first($accounts);
        }

        // finally return null.
        return null;
    }

    /**
     * Extract the Avatar URL from the Steem account provided.
     *
     * @return null|string
     */
    public function handle()
    {
        // locate the account on Steem.
        try {
            $account = $this->getAccount();
            // assign null if error.
        } catch (\Exception $e) {
            $account = null;
        }

        // abort if no account was found.
        if (!$account) {
            return null;
        }

        // try extracting the profile image URL.
        try {
            // extract meta.
            $meta = json_decode(array_get($account, 'json_metadata', '{}'), true);
            // return the profile image, if any.
            $url = array_get($meta, 'profile.profile_image', null);
            // otherwise.
        } catch (\Exception $e) {
            // assign null if exception.
            $url = null;
        }

        // return the URL variable, even if null.
        return $url;
    }
}
