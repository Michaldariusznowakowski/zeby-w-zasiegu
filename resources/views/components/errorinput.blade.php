@props(['name'])
@error($name)
    <span class="error">{{ $message }}</span>
@enderror
