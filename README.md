# Network


## How It Works
Mostly, this workflow is a wrapper around the `networksetup` command that interfaces with the network cards in your Mac. It also makes use of the `airport` CLI utility to scan for networks.

## Speed
Most information is retrieved quickly, but scanning for Wi-Fi networks takes a few seconds, just like it does everywhere else. Also,

## Permissions
Sometimes `networksetup` requires administrator permissions, and so we'll need to use SUDO. This will require that your user account has administrative permissions. To re-use the password, the workflow will save the password (securely) in the keychain. If you change your password, then you'll have to re-enter it.

## Joining Networks

### Passwords

Joining a password-protected network requires that you provide a password. Since we cannot access the values already in the keychain programmatically, we have to set them the first time that we try to join a network. The workflow will then save the password in the keychain in an entry that it has the permission to retrieve.

## Bugs
Sometimes this fails when scanning for certain networks. I'm not perfectly sure what input is killing it, but the problem is that the plist data returned by the `airport` command kills the plist parser. Maybe there is some fucked up XML that the parser cannot handle.

## Todo
(Pull requests welcome)
* Make this work if more than one Wi-Fi device is present. (Hard to test because I don't have this setup on anything, anywhere, at anytime).
* Implement joining unprotected wifi networks
* Bring in security data into the first display
* Test Proxy Data
* Add in Enterprise profiles (requires creating and importing XML files; might be problematic as many of the `networksetup` commands )
* Add in ability to input a new password on an already known network