# ActiveCollab Alfred 3 Workflow

Quickly jump to a project in ActiveCollab.  

## Installation

Grab the `.alfredworkflow` file or symlink this stuff if you wish to work or debug on it.   
I use the awesome `install-workflow.py` from @deanishe. 
See [https://gist.github.com/deanishe/35faae3e7f89f629a94e](https://gist.github.com/deanishe/35faae3e7f89f629a94e).   
Use like this:

```bash
workflow-install -s src
```

## Setup 

Make sure to add the environment variables `AC_ORG_NAME`, `AC_USERNAME`, `AC_PASSWORD`,
`AC_SELFHOSTED_URL`. (click the `[x]` icon in Alfred with the workflow selected)

## Usage

Search for projects with the prefix `ac`. For example:

```
ac github
```

Note that the first time it might be a little slow since it's fetching live results from
ActiveCollab, but subsequent calls are cached.  
Which brings us to the following:

Having typed the `ac` prefix without argument, you'll notice a `Clear local cache` option.  
Try this if you're missing projects or something.

## Todo

- Right now this only supports self hosted ActiveCollab installations. That's what my team uses so
    that's what I built. I might extend to support cloud login if there's interest.
- Might be fun to add more CRUD methods later? To add projects, tasks, what have you from Alfred? I
    dunno. It's just one input box, you know.


