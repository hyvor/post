## Hyvor Post

*No Tracking, Just Ideas, Delivered.*

[Hyvor Post](https://post.hyvor.com) is a simple, privacy-first newsletter platform. It is based on [Hyvor Relay](https://relay.hyvor.com), an open-source, self-hosted email API for developers.

<p align="center">
  <a href="https://post.hyvor.com">
    <img src="https://hyvor.com/api/public/logo/post.svg" alt="Hyvor Post Logo" width="130"/>
  </a>
</p>

<p align="center">
  <a href="https://post.hyvor.com">
    Newsletter Platform
  </a>
    <span> | </span>
    <a href="https://post.hyvor.com/docs">
    Docs
  </a>
</p>

## Note on Self-Hosting

While Hyvor Post is open-source, it is not yet ready for self-hosting. There are some pending tasks:

- [Set up OIDC](https://github.com/hyvor/internal?tab=readme-ov-file#openid-connect-setup) (PRs welcome, see [this](https://github.com/hyvor/relay/pull/159) and [this](https://github.com/hyvor/relay/pull/226))
- Self-hosting documentation

## Features

- **No Tracking**: No click or open tracking. Respect your subscribers' privacy.
- **Easy Editor**: Simple and clean rich text with markdown support editor to compose newsletters.
- **Multi-Tenancy**: Support for multiple tenants with scoped access.
- **Multiple Newsletters**: Users can create and manage multiple newsletters.
- **List Management**: Import and manage subscriber lists with ease.
- **Customizable Email Templates**: Use customizable email templates for your newsletters.
- **Signup Form**: Easily embed a signup form on your website.
- **Archive Website**: Automatically generated archive website for your newsletters.
- **Console API**: Automate newsletter management using the console API.

## Architecture

- **PHP + Symfony** for the API backend.
- **SvelteKit** and [**Hyvor Design System**](https://github.com/hyvor/design) for the frontend, embed, and archive website.
- **PGSQL** as the database.

## Community

- [HYVOR Community](https://hyvor.community)
- [Discord](https://hyvor.com/go/discord)

## Contributing

Visit [hyvor/dev](https://github.com/hyvor/dev) to set up the HYVOR development environment. Then, run `./run post` to start Hyvor Post at `https://post.hyvor.localhost`.

Directory structure:

- `/backend`: Symfony API backend
- `/frontend`: SvelteKit frontend
- `/embed`: Signup form embed code
- `/archive`: Newsletter archive website

## License

Hyvor Post is licensed under the [AGPL-3.0 License](https://github.com/hyvor/post/blob/main/LICENSE). When self-hosting is available, we will also offer [enterprise licenses](https://hyvor.com/enterprise) for organizations that require a commercial license or do not wish to comply with the AGPLv3 terms. See [Self-Hosting License FAQ](https://hyvor.com/docs/hosting-license) for more information.

![HYVOR Banner](https://raw.githubusercontent.com/hyvor/relay/refs/heads/main/meta/assets/hyvor-banner.svg)

Copyright © HYVOR. HYVOR name and logo are trademarks of HYVOR, SARL.

