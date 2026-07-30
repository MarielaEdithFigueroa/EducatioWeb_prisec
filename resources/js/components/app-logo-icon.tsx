import type { SVGAttributes } from 'react';

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg
            {...props}
            viewBox="0 0 40 40"
            xmlns="http://www.w3.org/2000/svg"
            aria-label="Educatio"
        >
            <circle cx="20" cy="20" r="20" fill="var(--brand-orange)" />
            <circle cx="20" cy="20" r="16" fill="var(--brand-blue-deep)" />
            <text
                x="20"
                y="27"
                textAnchor="middle"
                fill="white"
                fontSize="22"
                fontWeight="800"
                fontFamily="ui-sans-serif, system-ui, sans-serif"
            >
                E
            </text>
        </svg>
    );
}
