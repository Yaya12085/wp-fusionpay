const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
const { getSetting } = window.wc.wcSettings;
const { decodeEntities } = window.wp.htmlEntities;

const settings = getSetting("fusion_pay_data", {});

const defaultLabel = decodeEntities(settings.title) || "Fusion Pay";
const defaultDescription = decodeEntities(settings.description) || "";

const Label = (props) => {
  const { PaymentMethodLabel } = props.components;
  return <PaymentMethodLabel text={defaultLabel} />;
};

const Content = () => {
  return <div>{defaultDescription}</div>;
};

const FusionPayPaymentMethod = {
  name: "fusion_pay",
  label: <Label />,
  content: <Content />,
  edit: <Content />,
  canMakePayment: () => true,
  ariaLabel: defaultLabel,
  supports: {
    features: settings.supports || [],
  },
};

registerPaymentMethod(FusionPayPaymentMethod);
