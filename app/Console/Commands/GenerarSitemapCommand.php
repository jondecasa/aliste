<?php

namespace App\Console\Commands;

use App\Models\Noticia;
use App\Models\Pueblo;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class GenerarSitemapCommand extends Command
{
    protected $signature = 'sitemap:generar';

    protected $description = 'Genera public/sitemap.xml con todas las URLs públicas del sitio';

    public function handle(): int
    {
        $urls = collect();

        $urls->push($this->url(route('inicio'), now(), 'daily', '1.0'));
        $urls->push($this->url(route('pueblos'), now(), 'weekly', '0.8'));
        $urls->push($this->url(route('servicios'), now(), 'weekly', '0.8'));
        $urls->push($this->url(route('noticias'), now(), 'daily', '0.8'));
        $urls->push($this->url(route('contacto'), now(), 'monthly', '0.3'));
        $urls->push($this->url(route('cookies'), now(), 'yearly', '0.1'));
        $urls->push($this->url(route('privacidad'), now(), 'yearly', '0.1'));

        Pueblo::all()->each(function (Pueblo $pueblo) use ($urls) {
            $urls->push($this->url(route('pueblo', $pueblo), $pueblo->updated_at, 'weekly', '0.7'));
            $urls->push($this->url(route('pueblo.calendario', $pueblo), $pueblo->updated_at, 'daily', '0.6'));
            $urls->push($this->url(route('pueblo.gente', $pueblo), $pueblo->updated_at, 'weekly', '0.5'));
        });

        Noticia::all()->each(function (Noticia $noticia) use ($urls) {
            $urls->push($this->url(route('noticia', $noticia), $noticia->updated_at, 'monthly', '0.6'));
        });

        File::put(public_path('sitemap.xml'), $this->construirXml($urls));

        $this->info("sitemap.xml generado con {$urls->count()} URLs.");

        return self::SUCCESS;
    }

    /**
     * @return array{loc: string, lastmod: string, changefreq: string, priority: string}
     */
    private function url(string $loc, ?Carbon $lastmod, string $changefreq, string $priority): array
    {
        return [
            'loc' => $loc,
            'lastmod' => ($lastmod ?? now())->toAtomString(),
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    }

    /**
     * @param  Collection<int, array{loc: string, lastmod: string, changefreq: string, priority: string}>  $urls
     */
    private function construirXml(Collection $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars($url['loc'], ENT_XML1 | ENT_COMPAT, 'UTF-8')."</loc>\n";
            $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
            $xml .= "    <changefreq>{$url['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$url['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>'."\n";

        return $xml;
    }
}
