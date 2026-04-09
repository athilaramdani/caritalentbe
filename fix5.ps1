$text = Get-Content initialize.bat
$inSeeder = $false
for ($i=0; $i -lt $text.Length; $i++) {
    if ($text[$i] -match "DB::table\('genres'\^\)-\^>truncate") { $inSeeder = $true }
    if ($inSeeder -and $text[$i] -match "echo") {
        $text[$i] = $text[$i] -replace "!", "."
    }
}
$text | Set-Content initialize.bat
