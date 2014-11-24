Lemming.AssetProcessor
======================

Use this package to preprocess uploaded files by shell commands. A typical use case is image optimization.

Example configuration:
```
Lemming:
  AssetProcessor:
    MediaTypes:
      image/jpeg:
        - /opt/local/bin/jpegoptim --strip-all -f -q {}
      image/png:
        - /opt/local/bin/optipng -fix -q {}
```