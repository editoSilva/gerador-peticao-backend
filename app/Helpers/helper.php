
<?php 

if(!function_exists('formatarTextoChatGPT')) {
    function formatarTextoChatGPT(string $texto): string {
        // 1. Quebras de linha podem não existir, vamos garantir elas:
        // Aqui adiciona \n após os itens de lista e após os títulos para separar linhas
        $texto = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $texto); // negrito
    
        // Adiciona quebra de linha após listas iniciadas por "- "
        $texto = preg_replace('/(?<=\S)\s*-\s*/', "\n- ", $texto);
    
        // Garante que após cada "número. texto:" tenha uma quebra de linha
        $texto = preg_replace('/(\d+\.\s.*?:)/', "\n$1\n", $texto);
    
        // Agora divide em linhas pelo \n
        $linhas = preg_split('/\r\n|\r|\n/', $texto);
    
        $html = '';
        $inList = false;
    
        foreach ($linhas as $linha) {
            $linha = trim($linha);
            if (empty($linha)) continue;
    
            // Se linha começa com -, é item de lista
            if (str_starts_with($linha, '-')) {
                if (!$inList) {
                    $html .= "<ul>\n";
                    $inList = true;
                }
                $item = trim(substr($linha, 1));
                $html .= "<li>$item</li>\n";
            } else {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                // Se for título com <strong>, usa h3
                if (preg_match('/^<strong>(.*?)<\/strong>$/', $linha, $matches)) {
                    $html .= "<h3>{$matches[1]}</h3>\n";
                } else {
                    $html .= "<p>$linha</p>\n";
                }
            }
        }
    
        // Fecha lista caso tenha ficado aberta
        if ($inList) {
            $html .= "</ul>\n";
        }
    
        return $html;
    }
}

if(!function_exists('limparMarkdown')) {
    function limparMarkdown(string $texto): string
    {
        // Remove títulos '### ' (três cerquilhas com espaço)
        $texto = preg_replace('/^###\s*/m', '', $texto);
    
        // Remove negrito '**texto**' (remove os asteriscos, mantendo o texto)
        $texto = preg_replace('/\*\*(.*?)\*\*/s', '$1', $texto);
    
        // Remove os hífens de listas, mantendo o texto
        $texto = preg_replace('/^\s*-\s*/m', '', $texto);
    
        // Remove parênteses e seu conteúdo (opcional, se quiser tirar observações)
        // $texto = preg_replace('/\([^)]*\)/', '', $texto);
    
        // Remove espaços em branco extras no começo e fim de linhas
        $texto = preg_replace('/^[ \t]+|[ \t]+$/m', '', $texto);
    
        // Remove linhas em branco extras (duas ou mais quebras)
        $texto = preg_replace("/\n{2,}/", "\n\n", $texto);
    
        return trim($texto);
    }
}

if(!function_exists('saudacaoPorHorario')) {
    function saudacaoPorHorario(): string {
        $hora = (int) date('H');
    
        if ($hora >= 5 && $hora < 12) {
            return 'Bom dia';
        } elseif ($hora >= 12 && $hora < 18) {
            return 'Boa tarde';
        } else {
            return 'Boa noite';
        }
    }
    
}
