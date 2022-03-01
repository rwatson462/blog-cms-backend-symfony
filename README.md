# blog-cms-backend-symfony
A collection of APIs written with the Symfony framework to service a React CMS front end.

> Note: This is very much a work in progress in my spare time.  Please do not expect this to be a
  polished and finished article

## API implementation
> If anyone wants to see the code _I_ have written, see these files

### Authentication

The APIs all use a JWT (possibly a minor variation of) for authentication: The client must send
a token signed with the user's secret (their password encrypted with SHA256) that includes their
username as part of the payload.  The APIs then validate the signature and trust the contents
if they match.  *The contents should never be such that they allow influence over the system*, only
being used for authentication.  Post data should be sent as actual commands to use.

See `src/RWA/JWT/Token.php` for this implementation.

> Note: In this demo, I haven't included any database connection so the username/password
  combination that is checked is hard coded

As the APIs don't ever create a token, I've not written that functionality.

### Encoding

Part of the JWT spec requires sending "Base 64 URL encoded strings".  This is a slight extension
to Base64 in that is replaces the characters `+` and `/` with `-` and `_` (respectively).

I've created a very basic static class (just for laziness to use the autoloader on demand) in
`src/Util/Base64.php`

### APIs

Each API should return a JSON structure of the simplest form to allow the font end to operate.

The APIs created so far are:

`/login` to log a user in.  Requires a signed JWT token containing a username, signed with that
user's password.  The API then fetches the user's password from storage and re-signs the token with
that password to confirm the 2 passwords (used by the user, and used by the server) are the same.
Currently SHA256 is used but that can easily be switched out if the need arises (e.g. if collisions
become a problem, or if SHA256 itself becomes vulnerable like MD5 has before).

`/articles` to fetch a list of all articles currently listed in the blog.  Requires signed JWT token
that is validated in the same way as the login route (by re-signing).

## Boilerplate code

> If anyone wants to see the code _I_ have written, please ignore these files

Symfony includes a lot of boilerplate code that I haven't touched, this list of files should be
exhaustive:

* .gitignore
* composer.json
* composer.lock
* symfont.lock
* bin/console
* config/*
* public/index.php
* src/Kernel.php
