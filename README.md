# Plex TV Episode Collector

This PHP script adds specific TV show episodes to an existing collection in your Plex Media Server using its API. It is built using Guzzle HTTP client to send API requests to Plex, which can dynamically update metadata and manage collections for episodes.

## Features

- Automatically adds TV episodes to a designated collection.
- Uses the Plex API to interact with your Plex Media Server.
- Handles requests to add an episode to a collection by episode `ratingKey`.
- Example for adding an episode to a collection based on metadata.

## Prerequisites

Before you run the script, ensure that you have the following:

1. **Plex Media Server**: You need a running instance of Plex with API access.
2. **Plex Token**: You will need to authenticate with the Plex API using your Plex token. You can retrieve the Plex token by inspecting network requests in your Plex web app or following [this guide](https://support.plex.tv/articles/204059436-finding-an-authentication-token-x-plex-token/).
3. **PHP**: Make sure you have PHP installed on your machine.
4. **Guzzle HTTP Client**: This script uses the Guzzle HTTP client, which can be installed via Composer.

### Installing Guzzle

```bash
composer require guzzlehttp/guzzle
```

## Usage

1. Clone the Repository

Clone this repository to your local machine:
```bash
git clone https://github.com/Tallahasseean/plex-collector.git
cd plex-collector
```

2. Edit the Script
   
Open the script and edit the following values to match your Plex setup:

`$plexHost`: Your Plex server IP address (e.g., 'http://192.168.4.189').

`$plexPort`: The port used by your Plex server, typically 32400.

`$token`: Your Plex API token.

`$libraryName`: The name of the Plex library section where the shows are located (e.g., `TV Shows`).

`$collectionName`: The name of the collection you want to add episodes to (e.g., `Thanksgiving`).

`$keyword`: The keyword to search for in episode titles and descriptions (e.g., `thanksgiving`).

3. Run the Script

Once you've configured the script, run it via the command line:

```bash
php collect.php
```

## API Endpoints Used

This script uses the following Plex API endpoints:

- Fetch Server Identifier: `/servers`
- Get Library Sections: `/library/sections`
- Fetch All TV Shows: `/library/sections/{sectionId}/all`
- Fetch Episodes for Seasons: `/library/metadata/{seasonId}/children`
- Add Episode to Collection: `/library/collections/{collectionId}/items`

## Debugging

If the script encounters issues or fails to add episodes to the collection, check the following:

- API Token: Ensure your Plex token is valid and has the necessary permissions.
- Log Output: If a 400 Bad Request error occurs, inspect the response body for further details.
- Plex Logs: Check your Plex Media Server logs for more details on API requests.
Contributing

Feel free to contribute to this project by submitting a pull request or creating an issue. Any suggestions or improvements are welcome!

## License

```
         DO WHAT THE F*CK YOU WANT TO PUBLIC LICENSE
                   Version 2, December 2004
 
Copyright (C) 2004 Sam Hocevar <sam@hocevar.net>

Everyone is permitted to copy and distribute verbatim or modified
copies of this license document, and changing it is allowed as long
as the name is changed.
 
           DO WHAT THE F*CK YOU WANT TO PUBLIC LICENSE
  TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION

 0. You just DO WHAT THE F*CK YOU WANT TO.
```
