Minecraft 3D Skin Renderer (php)
================================

Render a 3D view of a Minecraft skin using PHP.

Project first developed by <a href="https://github.com/supermamie/php-Minecraft-3D-skin" target="_blank">supermamie</a>.
The first intention of this fork was to translate the whole php file into English, however it ended with a bigger refactoring of the code to allow for other enhancements.

### Example of URL:
`http://example.com/cf-mcskin.php?vr=-25&hr=-25&hrh=0&vrla=0&vrra=0&vrll=0&vrrl=0&ratio=12&format=png&displayHair=true&headOnly=false&user=cajogos`

## GET Parameters
- `user` - Minecraft's username for the skin to be rendered.
- `vr` - Vertical Rotation
- `hr` - Horizontal Rotation
- `hrh` - Horizontal Rotation Head
- `vrll` - Vertical Rotation Left Leg
- `vrrl` - Vertical Rotation Right Leg
- `vrla` - Vertical Rotation Left Arm
- `vrra` - Vertical Rotation Right Arm
- `displayHair` - Either or not to display hairs. Set to "false" to NOT display hairs (this includes helmets).
- `headOnly` - Either or not to display the ONLY the head. Set to "true" to display ONLY the head (and the hair, based on displayHair).
- `format` - The format in which the image is to be rendered. PNG ("png") is used by default set to "svg" to use a vector version.
- `ratio` - The size of the "png" image. The default and minimum value is 2.

## TODO
- By request, allow the implementation of the 3-pixel wide Alex skin.
