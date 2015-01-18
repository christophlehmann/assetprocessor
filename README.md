Lemming.AssetProcessor
======================

Use this package to pre-process uploaded files by shell commands. A typical use case is image optimization.

Example configuration:
```
Lemming:
  AssetProcessor:
    ProcessOnUpload: TRUE
    MediaTypes:
      image/jpeg:
        - /opt/local/bin/jpegoptim --strip-all -f -q {}
      image/png:
        - /opt/local/bin/optipng -fix -q {}
```

For post-processing use the flow command:
```
./flow assetprocessor:run
```