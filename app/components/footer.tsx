// components/Footer.tsx
import { FaFacebookF, FaInstagram } from "react-icons/fa";

type FooterProps = {
  siteName?: string;
  tagline?: string;
  instagramUrl?: string;
  facebookUrl?: string;
  year?: number;
};

export default function Footer({
  siteName = "Coudelaria Lima Monteiro",
  tagline = "Tradição no campo. Cavalos com alma.",
  instagramUrl = "https://www.instagram.com/coudelaria_lima_monteiro/",
  facebookUrl = "https://www.facebook.com/coudelarialimamonteiro/",
  year = new Date().getFullYear(),
}: FooterProps) {
  return (
    <footer className="bg-[#0F172A] text-[#E5E7EB]">
      <div className="mx-auto max-w-5xl px-6 py-8">
        <div className="flex flex-col items-center gap-3 text-center">
          <span className="text-sm font-semibold tracking-wide text-white">
            {siteName}
          </span>

          <span className="text-xs opacity-80">{tagline}</span>

          {/* Ícones */}
          <div className="flex items-center gap-4">
            <a
              href={instagramUrl}
              target="_blank"
              rel="noopener noreferrer"
              aria-label="Instagram"
              title="Instagram"
              className="rounded-full border border-white/10 p-2 transition-colors hover:border-[#16A34A]/40 hover:text-[#16A34A]"
            >
              <FaInstagram className="h-4 w-4" />
            </a>

            <a
              href={facebookUrl}
              target="_blank"
              rel="noopener noreferrer"
              aria-label="Facebook"
              title="Facebook"
              className="rounded-full border border-white/10 p-2 transition-colors hover:border-[#16A34A]/40 hover:text-[#16A34A]"
            >
              <FaFacebookF className="h-4 w-4" />
            </a>
          </div>
        </div>

        <div className="mx-auto mt-6 h-px w-40 bg-[#16A34A] opacity-35" />

        <div className="mt-4 text-center">
          <span className="text-[11px] opacity-70">
            © {year} · Todos os direitos reservados
          </span>
        </div>
      </div>
    </footer>
  );
}
