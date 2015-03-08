php Minecraft 3D Skin Renderer
=====================

Render a 3D view of a Minecraft skin using PHP.

Project first developed by <a href="https://github.com/supermamie/php-Minecraft-3D-skin" target="_blank">supermamie</a>. The intention of this fork was to translate the whole php file into English.

I have also used the traditional php naming conventions whenever needed for variable names using the underscore notation instead of the camel notation used previously.

### Example of URL:
`http://example.com/cf-mcskin.php?vr=-25&hr=-25&hrh=0&vrla=0&vrra=0&vrll=0&vrrl=0&ratio=12&format=png&displayHair=true&headOnly=false&user=cajogos`


## Changes Made
### GET Parameters Translations
The main reason why I wanted to translate this project was the fact that the previous GET parameters were not understandable and it had me try and guess what they all mean, therefore I have also renamed the parameters to match their English translations.

- `login` = `user` - Minecraft's username for the skin to be rendered.
- `a` = `vr` - Vertical Rotation
- `w` = `hr` - Horizontal Rotation
- `wt` = `hrh` - Horizontal Rotation Head
- `ajg` = `vrll` - Vertical Rotation Left Leg
- `ajd` = `vrrl` - Vertical Rotation Right Leg
- `abg` = `vrla` - Vertical Rotation Left Arm
- `abd` = `vrra` - Vertical Rotation Right Arm
- `displayHairs` = `displayHair` - Either or not to display hairs. Set to "false" to NOT display hairs.
- `headOnly` (Remained unchanged) - Either or not to display the ONLY the head. Set to "true" to display ONLY the head (and the hair, based on displayHair).
- `format` (Remained unchanged) - The format in which the image is to be rendered. PNG ("png") is used by default set to "svg" to use a vector version.
- `ratio` (Remained unchanged) - The size of the "png" image. The default and minimum value is 2.

### Variable Translations
For this to work I had to make some variable changes in the code. I converted any French named variables into their English translation.

### times[] Array Renaming
The Array "times[]" has been fully renamed into their English translations, below there is a list of the name changes:

- Telechargement-Image - Download-Image
- Calculs-Angles - Angle-Calculations
- Determination-des-faces - Determination-of-faces
- Generation-polygones - Polygon-generation
- Rotation-membres - Members-rotation
- Calcul-affichage-faces - Calculated-display-faces
