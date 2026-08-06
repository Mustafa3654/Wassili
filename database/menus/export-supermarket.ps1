$ErrorActionPreference = 'Stop'

$conn = New-Object System.Data.SqlClient.SqlConnection('Server=localhost;Database=data2026;Integrated Security=True;')
$conn.Open()

$cmd = $conn.CreateCommand()
$cmd.CommandText = "SELECT Item_Desc, Sale_Price, Sal_Curr_Id FROM Items WHERE Sale_Price > 0 ORDER BY Item_Desc"
$reader = $cmd.ExecuteReader()

$items = New-Object System.Collections.Generic.List[object]
while ($reader.Read()) {
    $desc = [string]$reader['Item_Desc']
    $price = [double]$reader['Sale_Price']
    $curr = [int]$reader['Sal_Curr_Id']

    if ($curr -eq 1) {
        $price = [math]::Round($price / 90000.0, 2)
    } else {
        $price = [math]::Round($price, 2)
    }

    $items.Add(@{ name = $desc; name_ar = $desc; price = $price })
}

$reader.Close()
$conn.Close()

$json = @{
    category = @{ slug = 'supermarket'; name = 'Supermarket'; name_ar = 'سوبر ماركت' }
    items    = $items
} | ConvertTo-Json -Depth 3 -Compress

$out = Join-Path $PSScriptRoot 'supermarket-items.json'
$utf8 = New-Object System.Text.UTF8Encoding($false)
[System.IO.File]::WriteAllText($out, $json, $utf8)

Write-Host "Exported $($items.Count) items to $out"
