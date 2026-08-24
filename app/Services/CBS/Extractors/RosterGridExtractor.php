<?php

namespace App\Services\CBS\Extractors;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Collection;
use Illuminate\Http\Client\Response;

class RosterGridExtractor
{
    public static function from(string $html)
    {
        $dom = new DOMDocument();
        
        // Suppress warnings for malformed HTML
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        
        // Find the main roster grid table
        $tables = $xpath->query('//table[contains(@class, "data") or contains(@id, "roster") or contains(@class, "roster")]');
        
        if ($tables->length === 0) {
            // Fallback: look for any table with team/player data
            $tables = $xpath->query('//table[.//td[contains(@class, "player") or contains(@class, "team")]]');
        }
        
        if ($tables->length === 0) {
            return ['teams' => [], 'error' => 'No roster table found'];
        }
        
        $table = $tables->item(0);
        $teams = [];
        
        // Get table headers to understand structure
        $headers = $xpath->query('.//thead//th | .//tr[1]//th | .//tr[1]//td[contains(@class, "header")]', $table);
        $teamHeaders = [];
        
        foreach ($headers as $index => $header) {
            $headerText = trim($header->textContent);
            if (!empty($headerText) && $headerText !== 'Player' && $headerText !== 'Position') {
                $teamHeaders[$index] = $headerText;
            }
        }
        
        // Get all data rows
        $rows = $xpath->query('.//tbody//tr | .//tr[position()>1]', $table);
        
        foreach ($rows as $row) {
            $cells = $xpath->query('.//td', $row);
            
            if ($cells->length === 0) continue;
            
            // First cell usually contains player info
            $playerCell = $cells->item(0);
            $playerInfo = self::extractPlayerInfo($playerCell, $xpath);
            
            if (empty($playerInfo['name'])) continue;
            
            // Process team columns
            foreach ($teamHeaders as $colIndex => $teamName) {
                if ($colIndex < $cells->length) {
                    $teamCell = $cells->item($colIndex);
                    $isOnTeam = self::isPlayerOnTeam($teamCell, $xpath);
                    
                    if ($isOnTeam) {
                        if (!isset($teams[$teamName])) {
                            $teams[$teamName] = [
                                'name' => $teamName,
                                'players' => []
                            ];
                        }
                        
                        $teams[$teamName]['players'][] = $playerInfo;
                    }
                }
            }
        }
        
        return [
            'teams' => array_values($teams),
            'total_teams' => count($teams),
            'extracted_at' => now()->toISOString()
        ];
    }
    
    private static function extractPlayerInfo($playerCell, DOMXPath $xpath)
    {
        $playerInfo = [
            'name' => '',
            'position' => '',
            'team' => '',
            'id' => null
        ];
        
        // Look for player name in various possible structures
        $nameElements = $xpath->query('.//a[contains(@href, "player")] | .//span[contains(@class, "player")] | .//div[contains(@class, "player")]', $playerCell);
        
        if ($nameElements->length > 0) {
            $nameElement = $nameElements->item(0);
            $playerInfo['name'] = trim($nameElement->textContent);
            
            // Extract player ID from href if available
            if ($nameElement instanceof \DOMElement && $nameElement->hasAttribute('href')) {
                $href = $nameElement->getAttribute('href');
                if (preg_match('/player[\/\-](\d+)/', $href, $matches)) {
                    $playerInfo['id'] = (int)$matches[1];
                }
            }
        } else {
            // Fallback: use cell text content
            $cellText = trim($playerCell->textContent);
            $lines = array_filter(array_map('trim', explode("\n", $cellText)));
            if (!empty($lines)) {
                $playerInfo['name'] = $lines[0];
            }
        }
        
        // Look for position info
        $positionElements = $xpath->query('.//span[contains(@class, "position")] | .//div[contains(@class, "position")]', $playerCell);
        if ($positionElements->length > 0) {
            $playerInfo['position'] = trim($positionElements->item(0)->textContent);
        } else {
            // Try to extract position from text patterns
            $cellText = $playerCell->textContent;
            if (preg_match('/\b(QB|RB|WR|TE|K|DEF|DST)\b/', $cellText, $matches)) {
                $playerInfo['position'] = $matches[1];
            }
        }
        
        // Look for team info
        $teamElements = $xpath->query('.//span[contains(@class, "team")] | .//div[contains(@class, "team")]', $playerCell);
        if ($teamElements->length > 0) {
            $playerInfo['team'] = trim($teamElements->item(0)->textContent);
        }
        
        return $playerInfo;
    }
    
    private static function isPlayerOnTeam($teamCell, DOMXPath $xpath)
    {
        // Check if cell contains player indicator (checkbox, icon, etc.)
        $indicators = $xpath->query('.//input[@type="checkbox"][@checked] | .//span[contains(@class, "check")] | .//div[contains(@class, "roster")]', $teamCell);
        
        if ($indicators->length > 0) {
            return true;
        }
        
        // Check for specific text content that indicates player is on team
        $cellText = trim($teamCell->textContent);
        if (!empty($cellText) && $cellText !== '-' && $cellText !== 'N/A') {
            return true;
        }
        
        // Check for specific CSS classes that indicate rostered player
        if ($teamCell instanceof \DOMElement) {
            $cellClass = $teamCell->getAttribute('class');
            if (strpos($cellClass, 'roster') !== false || strpos($cellClass, 'owned') !== false) {
                return true;
            }
        }
        
        return false;
    }
}
